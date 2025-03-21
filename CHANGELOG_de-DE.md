# 4.4.3
* Suchoption hinzugefügt
* Feeds:
    * Produkt-Feed
        * Neu hinzugefügt:
            * `extraDataList`.`*`
            * `extraData`.`parentId`
            * `extraData`.`displayGroup`
            * `extraData`.`manufacturerId`
            * `extraData`.`isCloseoutAvailable`
        * Geändert:
            * Translatable to use `translation('key')`
            * Moved auto mapped to `extraData`
        * Korrigiert:
            * `productnumber` => `productNumber`
            * `instock` => `inStock`
            * `imgurl` => `imgUrl`
    * Kategorie-Feed
        * Neu hinzugefügt:
            * `extraData`.`*`
        * Geändert:
            * Translatable to use `translation('key')`

# 4.4.2
* Veraltetes CSRF-Token entfernen
* Konfigurationsoption hinzugefügt, um eine Hallo-Einzelhandelsseite mit Kategorie-ID statt Kategoriename anzufordern
* Option hinzugefügt, damit Warenkorbempfehlungen im Warenkorb statt an der Seite angezeigt werden

# 4.4.1
* API-Anforderungsschutz und bessere Handhabung hinzugefügt
* Benutzerdefinierter Protokollhandler für die API-Anforderungen hinzugefügt

# 4.4.0
* Option hinzugefügt, Shopware-Produktlisten mithilfe des Seitenschlüssels durch Hello-Retail-Seiten zu ersetzen

# 4.3.1
* Bugfix: Sales Channel Context aus Exportnachricht entfernen

# 4.3.0
* Funktion zum Abrufen von Produktempfehlungen im OffCanvas-Warenkorb hinzugefügt.
* JavaScript-Empfehlungsblock durch serverseitigen API-Aufruf an Hello Retail ersetzt

# 4.2.1
* Fehler bezüglich der Serialisierung der Nachrichtenwarteschlange behoben

# 4.2.0
* Autorisierungstoken zu allen Feeds hinzugefügt. Gehen Sie zu den Hello Retail-Vertriebskanälen und generieren Sie ein Token

# 4.1.0
* Einige Fehler bei der Serialisierung der Nachrichtenwarteschlange behoben, die unter Shopware 6.5.x auftreten konnten

# 4.0.2
* Korrigieren Sie die Nachverfolgung, indem Sie das Skript in den Block „layout_head_javascript_tracking“ verschieben

# 4.0.1
* Fehlender Parameter für geplante Aufgaben für den Handler für geplante Aufgaben hinzugefügt

# 4.0.0
* Kompatibilität für Shopware 6.5

# 3.0.11
* Warenkorb-Tracker verbessern: Der Warenkorb wird jetzt auf jeder Seite verfolgt und der Offcanvas-Warenkorb ist geöffnet
