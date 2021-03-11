# sauvegardeEX_import_adapter
This is an input adapter for “live capturing” files from iOS / OS X / Android / FirefoxOS / (Linux) for Sauvegare 

# Purpose of this tool (tool chain)
This project consists of three scripts to enable live data acquisition from Systems with the operating system iOS / OS X / Android / FirefoxOS / (Linux) for the tool Sauvegarde and SauvegardeEx. The purpose of this project is to equip a system “ad-hoc” with a systematically file exfiltration and to import it to Sauvegarde. In the context of our research we use an extended version of this tool (called SauvegardeEx) to perform file-based security analysis.

# Requirements
- Das Untersuchungsobjekt benötigt lediglich einen shell Zugang und die vorinstallierten Tools fsmon und dd.
- Der Extration client needs an ssh-client, python and php
TODO: Insert figure

# Usage Video
TODO: Insert video

# Usage of the scripts (Step 1)
Connect to your Security Research Device via SSH and execute the following commands:
```
cd /tmp/
mkdir backup
cd backup
echo '#!/bin/sh' > script
echo 'string=$1 ' >> script
echo 'string2=${string/\/tmp\/backup/youwillneverfindthis}' >> script
echo 'randstr=$RANDOM$RANDOM$RANDOM$RANDOM'  >> script
echo 'stat $string2 >> /tmp/backup/$randstr.meta ' >> script
echo 'dd if=$string2 of=/tmp/backup/$randstr.data  bs=10  &' >> script
```

The local data backup can be started by executing:
```
fsmon | cut -f4 | xargs -n 1 bash -c 'bash /tmp/backup/script $@' bash
```

After your analysis you can stop the script with CTRL + C


# Usage of the scripts (Step 2)
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
python ssh_copy_client.py`
```

- to create from the files a "filecash.db" execute the following commands:
```
cp filecache_template.db filecache.db
php build_db.php
```
###################
Now you have a filecache.db containing your file version of your security device and you can it sync with cdpclient of Sauvegarde :D
###################

###################
WARNING: This Project is still in beta phase and is not ready for production
###################
