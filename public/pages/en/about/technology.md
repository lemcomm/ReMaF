Not only in its concepts, but also in its implementation is Might & Fealty ambitious and somewhat unique. Some of the technology employed for the game is familiar, some has rarely been seen in the context of games.

Frontend
--------

Front and center, Might & Fealty is a text-focused, browser-based, online role playing game played through any modern internet browser.

Eventually, we may offer alternative ways for you to interact with the game on a similarly complex level (like a full fledged API) or through a modified format suitable for smaller devices, but when, or if, those will ever happen remains to be seen.

Backend
-------

On the server-side, the game is built on the [Symfony 6](http://symfony.com) framework with a powerful [PostgreSQL](http://www.postgresql.org) database behind it.

But that is just scratching the surface. The real power for many parts of the game comes from running the game world on a full-blown GIS dataset. In less technical terms, Might & Fealty does not use artifical gamey maps, but the same technology that Google Maps and other real-world mapping systems use. In fact, the screenshot on this page is from [Quantum GIS](http://www.qgis.org/), a software used by universities, land developers and public service departments to handle real-world geospatial data.

Through the use of GIS, the game uses actual locations and distances instead of abstractions or tiles. Travel times and pathfinding work the same way as your navigation system and can react to a changing game-world where roads get built or bridges destroyed. The areas covered by estates are not arbitrary, but mathematically determined as the area closer to this settlement than to any other (voronoi cells). There is height, humidity, biome and other data for every spot on the map. All spiced up with a bit of fractal noise to make it more life-like.

This approach is also future-safe. In the future, we could decide to make borders between estates arbitrary or change the course of rivers or make deforestation possible or really any other change. Or we could go into more detail in the map and add smaller rivers, ponds or detail landscape features to any level we desire. In theory, we could go down to the level of individual trees without having to change anything in the backend engine.
