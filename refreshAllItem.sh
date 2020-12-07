rm log/log_Latest
touch log/log_Latest
date 1>> log/log_Latest
cd /var/www/html/
php refresh_item.php 1>> log/log_Latest
php refresh_shelters.php 1>> log/log_Latest
php refresh_shelters_mbw.php 1>> log/log_Latest
php refresh_emergency_rooms.php 1>> log/log_Latest
php refresh_pharmacies.php 1>> log/log_Latest
echo -e "\n" 1>> log/log_Latest
touch log/temp
cat log/log_Latest | cat - log/log_AllTime > log/temp && mv log/temp log/log_AllTime
