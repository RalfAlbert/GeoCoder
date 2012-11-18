# GeoCoder WordPress Map-Plugin #

GeoCoder ist ein WordPress-Plugin zur einfachen Intergration von Karten in Posts und auf Seiten via Shortcode. Im einfachsten Fall reicht der Shortcode `[gmap]` und die Angabe einer Adresse oder Geolocation (Latitude & Longitude) über ein Formular aus. Alle Einstellungen für die Kartendarstellung lassen sich bequem vorkonfigurieren.
Darüber hinaus können die Voreinstellungen jedoch individuell im Shortcode überschrieben werden. Dadurch erreicht man ein Maximum an Flexibilität.
Das Eingabeformular ermöglicht die Angabe von Adressen und/oder einer Geolocation (Latitude/Longitude). Dabei können Adressen ind Geolocations und Geolocations in Adressen konvertiert werden.

Desweiteren ermöglicht GeoCoder das Einbinden von Links zu Kartendiensten.

## Standardeinstellungen im Backend ##

Unter den Menüpunkt "*Einstellungen*" findet sich ein Eintrag "*Geocoder*". Hier können Voreinstellungen gesetzt werden die als Standard für die Kartenanzeige verwendet werden, Einstellungen für die RSS-Feeds gemacht werden und bei Bedarf ein API-Key eingegeben werden.

* Map Options

  Die Optionenen sollten weitestgehend selbsterklärend sein.

* RSS-Feeds

  GeoCoder kann Daten für die Geolocations in den bzw. die RSS-Feeds einfügen. Welche Formate eingefügt werden sollen, kann hier bestimmt werden.

* API-Key

  Google gibt Auskunft darüber wann ein API-Key benötigt wird. Siehe [https://developers.google.com/maps/documentation/javascript/tutorial#api_key](https://developers.google.com/maps/documentation/javascript/tutorial#api_key "Optaining an API key")



## Shortcode ##

Die Einbindung einer Karte erfolgt über einen Shortcode. Dazu muss als Minimumangabe im Eingabefeld eine Adresse oder eine Geolocation eingegeben werden. Unabhängig vom Eingabefeld und dern Grundeinstellungen können alle Einstellungen für die Karten im Shortcode überschrieben werden. Einige Parameter schliessen jedoch die Verwendung eines anderen parameters aus. So kann z.B. keine statische Übersichtskarte angelegt werden. Und auf statischen Karten können keine InfoWindows angezeigt werden. Tritt ein solcher Fall ein, so wird in der Beschreibung durch die Angabe "*Priorität vor [Option]*" darauf hingewiesen welche andere Option durch das setzen der entsprechenden Option überschrieben bzw. deaktiviert wird (alle Angaben in gescheiften Klammern (`{}`) sind optional).

`[gmap {center="lat,lng"} {adress="adress"} {latlng="lat,lng"} {info="text"} {zoom=0-21} {size=small|medium|large} {width=pixel} {height=pixel} {maptype=satellite|roadmap|terrain|hybrid} {format=png|jpg|gif} {static|dynamic} {generalmap}]`


* `center=[latitude],[longitude]` 

  Durch die `center` Option kann ein Punkt auf der Karte bestimmt werden auf den die Karte unabhängig vom Marker zentriert wird. Der Marker wird dennoch auf den durch das Eingabefeld bestimmten Punkt (bzw. durch die Parameter im Shortcode) gesetzt.

* `adress=[adresse]`

  Der Marker wird auf diese Adresse gesetzt. Sofern im Formular bereits eine Adresse angegeben ist, wird diese ignoriert.

* `latlng=[latitude],[longitude]`

  Der Marker wird auf die Geolocation gesetzt, die durch Latitude und Longitude bestimmt wird. Sofern im Formularfeld Latitude und Longitude angegeben sid, werden diese Werte ignoriert.

* `info=[text]`

  Sofern ein Infotext angegeben wird, wird dem Marker ein Infowindow hinzugefügt das beim Klick auf dem Marker aufgeht.

* `text=[text`

  Angabe eines Alternativtextes für statische Karten. Da die statischen Karten als Bild im HTML-Quelltext eingebunden werden, kann hiermit ein Alternativtext angegeben werden sofern die statische Karte nicht angezeigt werden kann. Die Voreinstellung ist "*GoogleMaps*"

* `zoom=[0-21]`

  Der Zoomwert für die Karte. Möglich sind ganzzahlige Werte zwischen 0 und 21.

* `size=[small|medium|large]`

  Auswahl einer der voreingestellten Standardgrößen `small`, `medium` oder `large`. Die genauen Abmessungen der Standardgrößen kann im Options-Menü vorgenommen werden.

* 'width=[pixel]`

  Die Breite der Karte in Pixel. Diese Option hat nur dann Priorität vor `size`, wenn auch für `height` (siehe unten) ein Wert angegeben ist.

* 'height=[pixel]`

  Die Höhe der Karte. Diese Option hat nur dann Priorität vor `size`, wenn auch für `width` (siehe oben) ein Wert angegeben ist.

* `maptype=[satellite|roadmap|terrain|hybrid]`

  Die Darstellungsart der Karte. Als Optionen stehen die vier Typen `satellite`, `roadmap`, `terrain` und `hybrid` zur Verfügung. Voreinstellung ist `roadmap`

* `format=[png|jpg|gif]`

  Das Bild-Format für statische Karten. Als Option stehen die drei Typen `png`, `jpg` und `gif` zur Verfügungh. Voreinstellung ist `png`

* `static` `dynamic`

    Mit den Optionen `static` und `dynamic` kann die Voreinstellung in den Standardeinstellungen überschrieben werden. Beide Optionen benötigen keine weiteren Parameter. `static` hat höhere Priorität als `dynamic`

* `generalmap`

  Erzeugen einer Übersichtskarte mit allen gespeicherten Geolocations. **Es werden jedoch nur die über das Eingabeformular gespeicherten Geolocations nberücksichtigt!** Karten die ihre Geolocations aus den Shortcode-Optionen beziehen, werden nicht berücksichtigt.



----------

## Roadmap für GeoCoder ##
 - Integration anderer Kartendienste (BingMaps, OpenStreetMap)
 - Interaktiver Dialog zur Erstellung von Karten
 - Mehrere Locations je Karte
 - Darstellungsbereich (Bounds) bestimmen
