#!/bin/bash

# set port
[[ -z "$2" ]] && port=8000 || port=$2


# func / start server
start() {
	php -S localhost:$port -t ./
}

# func / install
install() {
	php resource/tools.php install
}
install-db() {
	php resource/tools.php install-db
}

# func / make token
make-token()
{
	php resource/tools.php make-token
}


case "$1" in
	start)
		start
		;;

	install)
		install
		;;

	install-db)
		install-db
		;;

	make-token)
		make-token
		;;

	*)
		echo "Usage: ./dev-server.sh {start|install|install-db|make-token}" >&2
		exit 3
		;;
esac