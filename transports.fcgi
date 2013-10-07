#! /bin/bash

#
# auteur: Yann Lambert
# url:    https://github.com/wasapi/transports
#

QUERY=`echo $QUERY_STRING | sed -e "s/=/='/g" -e "s/&/';/g" -e "s/+/ /g" -e "s/%0d%0a/<BR>/g" -e "s/$/'/" `
eval $QUERY 

fonctionBUS()
{
  IFS=$'\n'

  url="http://www.ratp.fr/horaires/fr/ratp/bus/prochains_passages/PP/B$1/$2/$3"
  monbus=($(wget -qO - $url | grep -e "</td><td>" -e "Ligne $1"))
  echo "<table border="0">"
  echo "<tr><td colspan=\"2\"><img src=\"http://www.ratp.fr/horaires/images/networks/bus.png\"/> <img src=\"http://www.ratp.fr/horaires/images/lines/bus/$1.png\"/>"
  echo ${monbus[0]} | sed 's/^.*<span>\(.*\)<\/span>.*$/<b>\1<\/b>\&nbsp;<\/td><\/tr>/'
  i=1
  while [ "$i" -lt "${#monbus[*]}" ]
  do
    echo "${monbus[$i]}" | sed -e 's/^.\{14\}\(.*\).\{5\}$/<tr><td>\1\&nbsp;<\/td><\/tr>/' -e 's/<\/td><td>/\&nbsp;<\/td><td>/'
    ((i++))
  done
  echo "</table>"
  echo "<br />"
}

fonctionMETRO()
{
  IFS=$'\n'

  url="http://www.ratp.fr/horaires/fr/ratp/metro/prochains_passages/PP/$2/$1/$3"
  monmetro=($(wget -qO - $url | grep "</td><td>"))
  echo "<table border="0">"
  echo "<tr><td colspan="2"><img src=\"http://www.ratp.fr/horaires/images/networks/metro.png\"/> <img src=\"http://www.ratp.fr/horaires/images/lines/metro/$1.png\"/>"
  echo "<b>$2</b>&nbsp;</td></tr>"
  i=0
  while [ "$i" -lt "${#monmetro[*]}" ]
  do
    echo "${monmetro[$i]}" | sed -e 's/^.\{10\}\(.*\).\{5\}$/<tr><td>\1\&nbsp;<\/td><\/tr>/' -e 's/<\/td><td>/\&nbsp;<\/td><td>/'
    ((i++))
  done
  echo "</table>"
  echo "<br />"
}

fonctionRER()
{
  if [ $1 == A ] || [ $1 == B ]
  then
    IFS=$'\n'
    url="http://www.ratp.fr/horaires/fr/ratp/rer/prochains_passages/R$1/$2/$3"
    monrer=($(wget -qO - $url | grep '<a rel=\"facebox\"\|<td class=\"terminus\"\|<td class=\"passing_time\"'))
    echo "<table border="0">"
    echo "<tr><td colspan="3"><img src=\"http://www.ratp.fr/horaires/images/networks/rer.png\"/> <img src=\"http://www.ratp.fr/horaires/images/lines/rer/R$1.png\"/>"
    echo "<b>$2</b>&nbsp;</td></tr>"
    i=0
    while [ "$i" -lt "${#monrer[*]}" ]
    do
      if (( $i % 3 == 0 )); then echo "<tr>" | tr -d '\r\n'; fi;
      echo "${monrer[$i]}" | sed 's/^.*">\(.*\w\)<\/.*/<td>\1\&nbsp;<\/td>/' | tr -d '\r\n'
      ((i++))
      if (( $i % 3 == 0 )); then echo "</tr>"; fi;
    done
    echo "</tr>"
  elif [ $1 == C ] || [ $1 == D ] || [ $1 == E ]
  then
    echo -e "<tr><td colspan="2">La ligne $1 du rer est desservie par la SNCF<br />Utiliser fonctionTRANSILIEN</td></tr>"
  else
    echo -e "<tr><td colspan="2">La ligne $1 du RER n'existe pas</td></tr>"
  fi
  echo "</table>"
  echo "<br />"
}

fonctionTRANSILIEN()
{
  IFS=$'\n'

  if [[ -n $2 ]]
  then
    url="http://www.transilien.com/web/ITProchainsTrainsAvecDest.do?codeTr3aDepart=$1&codeTr3aDest=$2"
  else
    url="http://www.transilien.com/gare/$1"
  fi
  echo "<table border="0">"
  wget -qO - $url | dos2unix | sed -e 's/[\t]//g' -e '/^.*[ ]$/d' -e '/./!d' | sed -n -e '/titleBox_full/{s/^.*titleBox_full">\(.*\)<\/h1>/<tr><td colspan=4><b>Gare de \1<\/b><\/td><\/tr>/p}' -e '/<th class="ligne rer rer_a" scope="row">/{n;n;/^<img/s/^\(.*\)$/<tr><td>\1/p;n;n;/^<img/s/^\(.*\)$/\1<\/td>/p}' -e '/<td class="nom">/{p;n;n;s/\(.*\)/<td>\1<\/td>/p;n;n;p;n;n;n;n;n;n;n;s/\(.*\)/<td>\1<\/td><\/tr>/p}' | sed 's/\/styles\/images\/pictos/http:\/\/www.transilien.com\/styles\/images\/pictos/'
  echo "</table>"
  echo "<br />"
}

fonctionTRAMWAY()
{
  IFS=$'\n'

  url="http://www.ratp.fr/horaires/fr/ratp/tramway/prochains_passages/PP/T$1/$2/$3"
  montramway=($(wget -qO - $url | grep -e "</td><td>" -e "Ligne T$1"))
  echo "<table border="0">"
  echo "<tr><td colspan=\"2\"><img src=\"http://www.ratp.fr/horaires/images/networks/tramway.png\"/> <img src=\"http://www.ratp.fr/horaires/images/lines/tramway/T$1.png\"/>"
  echo ${montramway[0]} | sed 's/^.*<span>\(.*\)<\/span>.*$/<b>\1<\/b>\&nbsp;<\/td><\/tr>/'
  i=1
  while [ "$i" -lt "${#montramway[*]}" ]
  do
    echo "${montramway[$i]}" | sed -e 's/^.\{10\}\(.*\).\{5\}$/<tr><td>\1\&nbsp;<\/td><\/tr>/' -e 's/<\/td><td>/\&nbsp;<\/td><td>/'
    ((i++))
  done
  echo "</table>"
  echo "<br />"
}

echo "Content-type: text/html; charset=utf-8"
echo ""

case "$trajet" in
  sl)
    fonctionTRANSILIEN PARIS-SAINT-LAZARE-GARE-SAINT-LAZARE-8738400
    fonctionTRANSILIEN HAUSSMANN-SAINT-LAZARE-8728189
    fonctionMETRO 13 Saint\ Lazare R
    ;;
  ld)
    fonctionRER A Grande\ Arche\ la\ Defense R
    fonctionTRANSILIEN LA-DEFENSE-GRANDE-ARCHE-8738221
    ;;
  pl)
    fonctionTRAMWAY 3b Porte\ Des\ Lilas A
    fonctionBUS 105 105_438 A
    ;;
  *)
    echo "<a href=\"http://$HTTP_HOST$SCRIPT_NAME?trajet=sl\">Saint-Lazare</a><br />"
    echo "<a href=\"http://$HTTP_HOST$SCRIPT_NAME?trajet=ld\">La Defense</a><br />"
    echo "<a href=\"http://$HTTP_HOST$SCRIPT_NAME?trajet=pl\">Porte des Lilas</a><br />"
    ;;
esac
