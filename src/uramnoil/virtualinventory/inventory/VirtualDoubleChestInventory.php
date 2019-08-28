<?php


namespace uramnoil\virtualinventory\inventory;


use pocketmine\Player;
use uramnoil\virtualinventory\inventory\impersonator\DoubleChestImpersonator;
use uramnoil\virtualinventory\inventory\impersonator\Impersonator;

class VirtualDoubleChestInventory extends VirtualInventory {
	public function getName() : string {
		return "Virtual Double Chest Inventory";
	}

	public function getDefaultSize() : int {
		return 54;
	}

	public function createImpersonatorFrom(Player $impersonated) : Impersonator {
		return new DoubleChestImpersonator($impersonated, $this);
	}
}