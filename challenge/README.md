# challenge

ENV VARIABLES:
ADMIN_USERNAME ?: adminz
ADMIN_PASSWORD ?: ajdlaeahardadminpassword0987afjafh
INTERNAL PORT IS ALWAYS 80 CANNOT USE ENV VARIABLES FOR THAT

_All files related to challenge e.g. scripts/Dockerfiles/backend source etc should go in this folder._

**Files to be given to players must NOT be placed here.** They must be kept in [public/](public).

_You may include notes about challenge deployment/packaging here._

To build: docker build -t chal .
To run: docker run -p 3000:80 chal

TODO:
Changes to make in public(All done):
