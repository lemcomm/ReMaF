<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

class MapPOI {
	private string $name;
	private Polygon $geom;
	private int $id;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return MapPOI
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set geom
	 *
	 * @param polygon $geom
	 *
	 * @return MapPOI
	 */
	public function setGeom(Polygon $geom): static {
		$this->geom = $geom;

		return $this;
	}

	/**
	 * Get geom
	 *
	 * @return polygon
	 */
	public function getGeom(): Polygon {
		return $this->geom;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}
}
