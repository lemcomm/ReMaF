<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

class Cliff {
	private linestring $path;
	private ?int $id = null;

	/**
	 * Get path
	 *
	 * @return linestring
	 */
	public function getPath(): LineString {
		return $this->path;
	}

	/**
	 * Set path
	 *
	 * @param linestring $path
	 *
	 * @return Cliff
	 */
	public function setPath(LineString $path): static {
		$this->path = $path;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}
}
