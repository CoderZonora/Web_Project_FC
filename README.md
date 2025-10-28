# Project

To run:
clone the git repo
Run :  docker compose up -d
visit localhost:8080


TO exec into container:
docker exec -it xampp_charlie_message bash
/opt/lampp/bin/mysql -u root charlie_db -e "SELECT * from messages"