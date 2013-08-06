
	# This class is used to do some conversions not available from Ruby itself.
	# It handles time conversions needed in the server mainly from timestamp
	# to milliseconds and visa versa. 
	
module TimeHelper
	# Converts Milliseconds to Time
	def self.msToTime(ms)
		hours = (ms / (1000 * 60 * 60)).to_i
		minutes = ((ms - (hours * 1000 * 60 * 60))  / (1000 * 60)).to_i
		seconds = ((ms - (minutes * 1000 * 60) - (hours * 1000 * 60 * 60)) / (1000)).to_i
		milliseconds = (ms - (seconds * 1000) - (minutes * 1000 * 60) - (hours * 1000 * 60 * 60)).to_i

		time = "%02d:%02d:%02d:%03d" % [hours, minutes, seconds, milliseconds]
		return time
	end

	# Converts a time string to milliseconds
	def self.timeToMs(time)

		if time != 'TIMER VALUE'

			timeSplit = time.split(':')

			hours = timeSplit[0].to_i
			minutes = timeSplit[1].to_i
			seconds = timeSplit[2].to_i
			milliseconds = timeSplit[3].to_i

			hoursMS = hours * 60 * 60 * 1000
			minutesMS = minutes * 60 * 1000
			secondsMS = seconds * 1000

			totalMS = hoursMS + minutesMS + secondsMS + milliseconds

			return totalMS
	
		else
			return 0			
		end

	end
end