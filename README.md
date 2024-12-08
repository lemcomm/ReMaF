Might & Fealty
==============

This is the soon-to-be source code for the game [Might & Fealty](http://mightandfealty.com), a browser based, persistent, role playing game (RPG) with turn-based strategy elements, rewritten in PHP 8 on the Symfony 6 framework, using a PostgreSQL database for entity handling and QuantumGIS with PostGIS for map functionality. The original PHP 7 repository that used Symfony 2 is [available here](https://github.com/lemcomm/MaFCDR).

Originally, we had a text here recommending you didn't setup your own game server because it'd fracture the player base. Instead of that, we're going to recommend that you not setup your own _right now_ because it requires understanding how to work with GIS datasets, which is not intuitive to just try and learn. Give us some time though and we'll have a version of the game that can run without all of the super complex stuff on a simpler map.

Documentation
-------------

At this time, documentation for this code is still being written, however [some documentation](https://github.com/lemcomm/ReMaF/wiki) exists and some more can be found in the code itself.

Completeness
------------

The repository is **not** a complete standalone copy of the game. While it contains all the code, the game world data is missing. To create a game world, you need manual work in something like QuantumGIS, even if you do export a GeoJSON format from [Azgaar's Fantasy Map Generator](https://azgaar.github.io/Fantasy-Map-Generator/) to import into QGIS. When we're less focused on bug fixes and feature adding, its very likely we'll include a guide on how to import a GeoJSON map into a QGIS client that's connected to a game database, but that won't be soon, sorry.

License
-------------

![Attribution-NonCommercial-ShareAlike 4.0 International](https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png)

The code is provided under the [**CC BY-NC-SA 4.0** license](http://creativecommons.org/licenses/by-nc-sa/4.0/), the full legal text is in the file LICENSE.
In short, this license permits you to modify and re-distribute the code, with two limitations. First, you must include a reference, at least by name, to Might & Fealty and to Tom Vogt as the original creator. Secondly, you are not allowed to use the code or any derivatives commercially.
