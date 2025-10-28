To build: docker build -t chal .
To run: docker run -p 3000:80 -e ADMIN_USERNAME=admin -e ADMIN_PASSWORD=fake_password_dont_try_to_brute chal

If you get permission error try restarting the container.

For the admin bot just login as admin in a private window with creds you gave during docker container starting and visit url you want to visit. 
In actual deployment url's allowed to send to bot should begin with domain of challenge ( Eg: localhost:3000/...) so stick to that only.
