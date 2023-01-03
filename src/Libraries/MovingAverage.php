<?php

namespace App\Libraries;

class MovingAverage {

	private mixed $rr_size;
	private array $data;
	private int $index;
	private bool $has_data = false;

	public function __construct($size=5) {
		$this->data = array();
		$this->rr_size = $size;
		$this->index = 0;
	}

	public function addData($value): void {
		$this->index++;
		if ($this->index >= $this->rr_size) {
			$this->index = 0;
		}
		$this->data[$this->index] = $value;
		$this->has_data = true;
	}

	public function getAverage(): float|int {
		if (!$this->has_data) return -1;

		$total = 0;
		for ($i=0; $i<$this->rr_size; $i++) {
			if (isset($this->data[$i])) {
				$total += $this->data[$i];
			} else {
				$total += $this->data[$this->index];
			}
		}
		return $total / $this->rr_size;
	}

}

