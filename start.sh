apt-get update
apt-get install -y docker.io git

docker swarm init

git clone https://github.com/MikroGovernment/angaryos-stack.git

cd angaryos-stack/

chmod 755 -R ./frontend/
chmod 777 -R ./backend/storage/
chmod 777 -R ./backend/storage/logs/
chmod 777 -R ./backend/public/
chmod 777 -R ./backend/bootstrap/cache/
chmod 777 -R ./services/

chmod 600 ./services/postgresql/.pgpass
chown 26:26 ./services/postgresql/.pgpass

chmod 777 ./payload.sh
chmod +x ./payload.sh

docker stack deploy --compose-file ./docker-stack.yml angaryos