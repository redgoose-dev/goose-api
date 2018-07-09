#!/bin/bash

# set port
[[ -z "$2" ]] && port=8000 || port=$2


# func / start server
start() {
	php -S localhost:$port -t ./
}


case "$1" in
	start)
		start
		;;

	ready)
		php resource/tools.php ready
		;;

	install)
		php resource/tools.php install
		;;

	make-token)
		php resource/tools.php make-token
		;;

	*)
		echo "Usage: ./dev-server.sh {start|ready|install|make-token}" >&2
		exit 3
		;;
esac