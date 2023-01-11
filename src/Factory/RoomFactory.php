<?php

namespace App\Factory;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Room>
 *
 * @method        Room|Proxy create(array|callable $attributes = [])
 * @method static Room|Proxy createOne(array $attributes = [])
 * @method static Room|Proxy find(object|array|mixed $criteria)
 * @method static Room|Proxy findOrCreate(array $attributes)
 * @method static Room|Proxy first(string $sortedField = 'id')
 * @method static Room|Proxy last(string $sortedField = 'id')
 * @method static Room|Proxy random(array $attributes = [])
 * @method static Room|Proxy randomOrCreate(array $attributes = [])
 * @method static RoomRepository|RepositoryProxy repository()
 * @method static Room[]|Proxy[] all()
 * @method static Room[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Room[]|Proxy[] createSequence(array|callable $sequence)
 * @method static Room[]|Proxy[] findBy(array $attributes)
 * @method static Room[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Room[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class RoomFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'owner' => null, // TODO add App\Entity\User ORM type manually
            'publicCode' => uniqid(),
            'color' => rand(1, 10) > 2 ? self::faker()->hexColor() : null,
            'comment' => rand(1, 10) > 2 ? self::faker()->text(100) : null,
            'address' => rand(1, 10) > 2 ? self::faker()->address() : null,
            'registeredReservationsOnly' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Room $room): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Room::class;
    }
}
