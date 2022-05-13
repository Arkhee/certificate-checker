# certificate-checker
## Introduction ##

Simple script to check a list of domains certificates for validity and alert when close to expire
This was designed to be a simple and rather straightforward way to check letsencrypt does renew my certificates
* Define a list of domains to watch
* Specify delay (ex. 30 days) 
* Launch the script and get an alert for domains whose certificate expire within delay.

## Requirements ##

This script makes a heavy use of command lines, it may or may not work on your host
The command lines executed were grabbed from here and there on the internet
If they do not work for your case I suggest you do the same

It requires following command lines : 
* timeout
* openssl

These two are rather basic, especially on webservers, I've tested them successfully on several mutualised web hosts

Off course PHP is required, though since this script is quite simple it should work with nearly any version.