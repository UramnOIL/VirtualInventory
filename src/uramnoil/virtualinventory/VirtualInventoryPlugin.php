<?php


namespace uramnoil\virtualinventory;


use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Utils;
use uramnoil\virtualinventory\extension\SchedulerTrait;
use uramnoil\virtualinventory\inventory\VirtualInventory;
use uramnoil\virtualinventory\listener\RegisterOwnerListener;
use uramnoil\virtualinventory\repository\OwnerRepository;
use uramnoil\virtualinventory\repository\OwnerRepositoryFactory;
use uramnoil\virtualinventory\repository\VirtualInventoryRepository;
use uramnoil\virtualinventory\repository\VirtualInventoryRepositoryFactory;
use uramnoil\virtualinventory\task\TransactionTask;

class VirtualInventoryPlugin extends PluginBase implements VirtualInventoryAPI {
	use SchedulerTrait;

	/** @var VirtualInventoryAPI */
	private $api;
	/** @var OwnerRepository */
	private $ownerRepository;
	/** @var VirtualInventoryRepository */
	private $inventoryRepository;

	public function onLoad() {
		$this->api = $this;
		$this->ownerRepository = (new OwnerRepositoryFactory($this))->create();
		$this->inventoryRepository = (new VirtualInventoryRepositoryFactory($this))->create();
	}

	public function onEnable() {
		$this->ownerRepository->open();
		$this->inventoryRepository->open();

		$this->getServer()->getPluginManager()->registerEvents(new RegisterOwnerListener($this), $this);
	}

	public function onDisable() {
		$this->ownerRepository->close();
		$this->inventoryRepository->close();
	}

	public function getAPI() : VirtualInventoryAPI {
		return $this->api;
	}

	public function findById(int $id, callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		Utils::validateCallableSignature(function(?VirtualInventory $inventory) : void{}, $onDone);

		$task = new TransactionTask(function() use($id) : ?VirtualInventory {
			return $this->inventoryRepository->findById($id);
		}, $onDone);

		$this->submitTask($task);
	}

	public function findByOwner(IPlayer $owner, callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		Utils::validateCallableSignature(function(array $inventories) : void{}, $onDone);

		$task = new TransactionTask(function() use($owner) : array {
			return $this->inventoryRepository->findByOwner($owner);
		}, $onDone);

		$this->submitTask($task);
	}

	public function delete(VirtualInventory $inventory, ?callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		isset($onDone) ?
			Utils::validateCallableSignature(function(?object $result) : void{}, $onDone)
			: $onDone = function(?object $result) : void {};

		$task = new TransactionTask(function() use($inventory) : void {
			if($this->isDisabled()) {
				throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
			}
			$this->inventoryRepository->delete($inventory);
		}, $onDone);

		$this->submitTask($task);
	}

	public function newInventory(IPlayer $owner, int $type, callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		$task = new TransactionTask(function() use ($owner, $type) : VirtualInventory {
			return $this->inventoryRepository->new($owner,$type);
		}, $onDone ?? function(VirtualInventory $inventory) : void {});

		$this->submitTask($task);
	}

	public function save(VirtualInventory $inventory, callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		$onDone !== null ?
			Utils::validateCallableSignature(function(object $noUse) : void{}, $onDone)
			: $onDone = function(object $onUse) : void {};

		$task = new TransactionTask(function() use ($inventory) : ?object {
			$this->inventoryRepository->save($inventory);
			return null;
		}, $onDone);

		$this->submitTask($task);
	}

	public function registerOwner(IPlayer $owner, ?callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}
		$this->ownerRepository->new($owner, $onDone);
	}

	public function unregisterOwner(IPlayer $owner, ?callable $onDone) : void {
		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}

		if($this->isDisabled()) {
			throw new VirtualInventoryException('VirtualInventoryPlugin is disabled.');
		}
		$this->ownerRepository->delete($owner, $onDone);
	}
}