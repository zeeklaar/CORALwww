require 'rubygems'
require 'socket'
require 'json'
require 'mysql2'

require './TimeHelper.rb'


#NOTE: To debug the server, run with sudo ruby -d Server.rb
#To actually run, DON'T USE -d! Threads will crash silently without -d
#using -d will cause the entire server to crash on client disconnect
#THIS SHOULD BE FIXED!

# reads the entire contents of a file or socket
def read_all(container)
	buffer = ""
	readSize = 128 # amount of data to read at a time

	while(!container.eof)

		tmp = container.readpartial(readSize)

		buffer = buffer + tmp

		if tmp.length < readSize
			break
		end

		tmp = ""

	end

	return buffer
end

# combine two pieces of JSON
def combineJSON(exJSON, incJSON)

	# Make sure we don't get parsing errors with new users
	if(exJSON == "")
		exJSON = "[]"
	end

	existingData = JSON.parse(exJSON)
	newData = JSON.parse(incJSON)

	for i in 0..newData.length - 1
		newSite = newData[i]
		newSitePages = newSite["pages"] # For page comparison
		newSiteName = newSite["title"]
		newSiteTime = TimeHelper.timeToMs(newSite["time"])

		siteFound = false

		for j in 0..existingData.length - 1
			existingSite = existingData[j]
			existingSitePages = existingSite["pages"] # For page comparison
			existingSiteName = existingSite["title"]
			existingSiteTime = TimeHelper.timeToMs(existingSite["time"])

			if newSiteName == existingSiteName
				siteFound = true

				# add up the site level times together
				combinedSiteTime = newSiteTime + existingSiteTime
				combinedSiteTimeString = TimeHelper.msToTime(combinedSiteTime)
				existingSite["time"] = combinedSiteTimeString

				# start comparing the pages
				for k in 0..newSitePages.length - 1
					newPage = newSitePages[k]
					newPageName = newPage["title"]
					newPageDate = newPage["date"]
					newPageTime = TimeHelper.timeToMs(newPage["time"])

					pageFound = false

					for l in 0..existingSitePages.length - 1
						existingPage = existingSitePages[l]
						existingPageName = existingPage["title"]
						existingPageDate = existingPage["date"]
						existingPageTime = TimeHelper.timeToMs(existingPage["time"])

						#if the two pages have the same name and date; add their values together
						if newPageName == existingPageName && newPageDate == existingPageDate
							pageFound = true

							combinedPageTime = newPageTime + existingPageTime
							combinedPageTimeString = TimeHelper.msToTime(combinedPageTime)
							existingPage["time"] = combinedPageTimeString
							existingSitePages[l] = existingPage

						end
					end

					# if the page doesn't match anywhere, add it onto the site object
					if !pageFound
						existingSitePages << newPage
					end
				end
			existingSite["pages"] = existingSitePages
			existingData[j] = existingSite
			end
		end
		
		# no match; add the new site right inot the existing data
		if !siteFound
			existingData << newSite
		end
	end

	return existingData
end

# Function to read all info from MySQL Database
def readListsDatabase(connection)
	data = Array.new

	whiteData = Array.new
	greenData = Array.new
	grayData = Array.new
	blackData = Array.new

	whiteObj = Hash.new
	greenObj = Hash.new
	grayObj = Hash.new
	blackObj = Hash.new

	# Query each table and store all of the data in arrays for JSON Serialization
	whiteResults = connection.query("SELECT * FROM whitelist")
	whiteResults.each do |row|
		whiteData.push(row['WebsiteURL'])
	end

	greenResults = connection.query("SELECT * FROM greenlist")
	greenResults.each do |row|
		greenData.push(row['WebsiteURL'])
	end

	grayResults = connection.query("SELECT * FROM graylist")
	grayResults.each do |row|
		grayData.push(row['WebsiteURL'])
	end

	blackResults = connection.query("SELECT * FROM blacklist")
	blackResults.each do |row|
		blackData.push(row['WebsiteURL'])
	end

	# Build each hash
	whiteObj[:table] = 'white'
	whiteObj[:data] = whiteData

	greenObj[:table] = 'green'
	greenObj[:data] = greenData

	grayObj[:table] = 'gray'
	grayObj[:data] = grayData

	blackObj[:table] = 'black'
	blackObj[:data] = blackData

	# Push each hash into the bigger data array for serialization
	data.push(whiteObj)
	data.push(greenObj)
	data.push(grayObj)
	data.push(blackObj)

	databaseJSON = JSON.generate(data)

	return databaseJSON

end

#handle a connection that gives content json
def handleContentData(incomingData)
	# Get the user of the incoming data
	user = incomingData["User"]

	# combine existing data with the new data 
	incomingJSON = incomingData["Data"]

  	if(@existingJSON != nil)
  		# turn the JSON into strings so that they don't get passed by reference

  		combinedJSON = combineJSON(@existingJSON, JSON.generate(incomingJSON))
  		toWrite = JSON.generate(combinedJSON)
  	else
  		toWrite = JSON.generate(incomingJSON)
  	end

	# write that data back into the database for the right user	

	@loginDatabase.query("UPDATE users SET data='" +toWrite+"' WHERE username='" + user +"'");

end

#handle a connection that gives login json
def handleLoginData(loginJSON, client)
	userID = loginJSON["userID"]
	passwordHash = loginJSON["passwordHash"]

	users = @loginDatabase.query("SELECT * FROM users")

	sleep(1) # wait a second so the client has time to wait for the response

	# Check the incoming data against all users, and if we have a match, send back an 'okay'
	users.each do |user|
		if(userID == user["username"] && passwordHash == user["password"])
			puts "okay"
			client.write("okay")

			#get the existingJSON once from the database
			@existingJSON = user['data']

			return
		end
	end

	# If we haven't given a response by now, send back a "bad" response
	client.write("bad")
end



# constantly accept new connections

server = TCPServer.new("172.16.0.145", 3000)

#NOTE: Will crash if it can't connect!
listsDatabase = Mysql2::Client.new(:host=>'127.0.0.1', :database=>'corallists', :username=>'CORALUser', :password=>'coralpassword')
@loginDatabase = Mysql2::Client.new(:host=>'127.0.0.1', :database=>'coralusers', :username=>'CORALUser', :password=>'coralpassword')

puts "Server Started"

loop do
  Thread.start(server.accept) do |client|

  	puts "Client Connected"

  	@existingJSON = nil
  	databaseJSON = nil

  	until client.closed?
  		puts 'running'

  		# Get incoming data from the client
  		data = read_all(client)

  		incomingJSON = JSON.parse(data)

  		toWrite = nil

  		# Get all the data from the database in JSON form
  		newestData = readListsDatabase(listsDatabase);

  		# If the newest data really is different than the stored JSON, then we throw that to the client
  		if(databaseJSON != newestData)

  			databaseJSON = newestData

  			client.write(databaseJSON)
  		end

  		# Check the type of incoming data

  		if(incomingJSON["Type"] == "Login")
  			handleLoginData(incomingJSON["Data"], client)
  		elsif(incomingJSON["Type"] == "Time")
  			handleContentData(incomingJSON)
  		else
  			puts "Unknown JSON Type"
  		end
  			
	end
end
end
