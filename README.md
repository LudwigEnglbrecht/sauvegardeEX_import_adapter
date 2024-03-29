# sauvegardeEX_import_adapter
This is an input adapter for “live capturing” files from iOS / OS X / Android / FirefoxOS / (Linux) for Sauvegare 

## Purpose of this tool (tool chain)
This project consists of three scripts to enable live data acquisition from Systems with the operating system iOS / OS X / Android / FirefoxOS / (Linux) for the tool Sauvegarde and SauvegardeEx. The purpose of this project is to equip a system “ad-hoc” with a systematically file exfiltration and to import it to Sauvegarde. In the context of our research we use an extended version of this tool (called SauvegardeEx) to perform file-based security analysis. The following figure illustrates the intention of the adapter:
![](sauvegarde_adapter.png)


## Requirements
- The target device only needs shell access and the pre-installed tools _fsmon_ and _dd_.
- The Extration client needs an _ssh-client_, _python2_ and _php_.


## Usage of the scripts on the target device (Step 1)
Connect to your Security Research Device via SSH and execute the following commands:
```
cd /tmp/
mkdir backup
cd backup
echo '#!/bin/sh' > script
echo 'string=$2 ' >> script
echo 'pid=$1 ' >> script
echo 'string2=${string/\/tmp\/backup/youwillneverfindthis}' >> script
echo 'randstr=$RANDOM$RANDOM$RANDOM$RANDOM'  >> script
echo 'stat $string2 >> /tmp/backup/$randstr.meta ' >> script
echo 'echo $pid >> /tmp/backup/$randstr.meta ' >> script
echo 'dd if=$string2 of=/tmp/backup/$randstr.data  bs=10  &' >> script
```

The local data backup can be started by executing:
```
fsmon | cut -f 2,4 | xargs -n 2 bash -c 'bash /tmp/backup/script $@' bash
```

After your analysis you can stop the script with CTRL + C

### Usage video of the scripts (Step 1)
![](vid1.gif)



## Usage of the scripts (Step 2)
To get the recorded data from the device perform the following steps:

- remove your secruity device from known hosts
```
ssh-keygen -f "/root/.ssh/known_hosts" -R "192.168.178.57"
```

- connect and login to the secruity device
```
ssh root@192.168.178.57
exit
```

- start python script to copy all files from the device
```
python ssh_copy_client.py
```

- to create from the aquired files a "filecache.db" execute the following commands:
```
cp filecache_template.db filecache.db
php build_db.php
```

**Now you have a filecache.db containing your file version of your security device and you can it sync with cdpclient of Sauvegarde :D**


### Usage video of the scripts (Step 2)
![](vid2.gif)



## Syncing with the filecache.db with Sauvegarde (Step 3)
```
# copy filecache.db into your cdpclient folder
# execute:
cdpclient -c ../client.conf
```
![](sauvegarde_sync.png)



# DISCLAIMER
WARNING: This Project is still in beta phase and is not ready for usage in production!
