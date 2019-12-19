for x in {1..10} ; do
  sleep 10
  [ $((x % 10)) -eq 0 ] && php scrape.php -a search
done