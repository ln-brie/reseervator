<?php

namespace App\Factory;

use App\Entity\Calendar;
use App\Factory\UserFactory;
use App\Repository\CalendarRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Calendar>
 *
 * @method        Calendar|Proxy create(array|callable $attributes = [])
 * @method static Calendar|Proxy createOne(array $attributes = [])
 * @method static Calendar|Proxy find(object|array|mixed $criteria)
 * @method static Calendar|Proxy findOrCreate(array $attributes)
 * @method static Calendar|Proxy first(string $sortedField = 'id')
 * @method static Calendar|Proxy last(string $sortedField = 'id')
 * @method static Calendar|Proxy random(array $attributes = [])
 * @method static Calendar|Proxy randomOrCreate(array $attributes = [])
 * @method static CalendarRepository|RepositoryProxy repository()
 * @method static Calendar[]|Proxy[] all()
 * @method static Calendar[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Calendar[]|Proxy[] createSequence(array|callable $sequence)
 * @method static Calendar[]|Proxy[] findBy(array $attributes)
 * @method static Calendar[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Calendar[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class CalendarFactory extends ModelFactory
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
            'name' => self::faker()->words(2, true),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Calendar $calendar): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Calendar::class;
    }
}
