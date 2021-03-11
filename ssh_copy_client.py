#!/usr/bin/env python

import paramiko

hostname = '192.168.178.57'
port = 22
username = 'root'
password = 'PASSWORD'

if __name__ == "__main__":
    #paramiko.util.log_to_file('paramiko.log')
    s = paramiko.SSHClient()
    s.load_system_host_keys()
    s.connect(hostname, port, username, password)
    stdin, stdout, stderr = s.exec_command('ls /tmp/backup')
    # print(stdout.read())
    with open("Output.txt", "w") as text_file:
        text_file.write("%s" % stdout.read())
	
	
    # Read each line from the Output.txt and copy the data
    # Using readlines()
    file1 = open('Output.txt', 'r')
    Lines = file1.readlines()
 
    count = 0
    # Strips the newline character
    for line in Lines:
        count += 1
        # print("Line{}: {}".format(count, line.strip()))
        # print("{}".format(line.strip()))
        stdin2, stdout2, stderr2 = s.exec_command('dd if=/tmp/backup/%s' % line.strip() )
        # print(stdout2.read())
        # print(stdout.read())
        with open(line.strip(), "w") as text_file2:
            text_file2.write("%s" % stdout2.read())
	
    s.close()
