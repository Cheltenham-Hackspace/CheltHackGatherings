#!/bin/bash


oldData="\/CheltHackGatherings\/gatherings"

echo "Type the path to the folder from which files are served on your server (eg
      /var/www/html)"
read serverFolder

echo "Type the base path of the Cheltenham Hackspace Gatherings file (Folder
      containing css/, js/, php/ etc...) relative to the HTTP base. (Eg. If
      files are served from /var/www/html and the base path is /var/www/html/CHG
      then you would type CHG) and press [Enter]"
read newData

echo "Type in your severs address (eg. localhost or 87.48.250.203 or
      testdomain.com) and press [Enter]"
read serverDomain

echo "---"
declare -a Files=("$serverFolder/$newData/php/login/login.php"
                  "$serverFolder/$newData/php/variables/User.php"
                  "$serverFolder/$newData/pages/error/404.html"
                  "$serverFolder/$newData/pages/error/403.html")

echo "$newDatalogin.php"
echo ${Files[0]}
echo "sed -i"

for i in "${Files[@]}"
do
   :
   echo "sed -i 's/$oldData/$newData/g' \"$i\""
done
