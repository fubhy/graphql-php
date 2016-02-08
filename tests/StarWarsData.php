<?php

namespace Fubhy\GraphQL\Tests;

/**
 * This defines a basic set of data for our Star Wars Schema.
 *
 * This data is hard coded for the sake of the demo, but you could imagine
 * fetching this data from a backend service rather than from hardcoded
 * JSON objects in a more complex demo.
 */
trait StarWarsData
{
    protected function getLukeSkyWalker()
    {
        return [
            'id' => '1000',
            'name' => 'Luke Skywalker',
            'friends' => ['1002', '1003', '2000', '2001'],
            'appearsIn' => [4, 5, 6],
            'homePlanet' => 'Tatooine',
        ];
    }

    protected function getDarthVader()
    {
        return [
            'id' => '1001',
            'name' => 'Darth Vader',
            'friends' => ['1004'],
            'appearsIn' => [4, 5, 6],
            'homePlanet' => 'Tatooine',
        ];
    }

    protected function getHanSolo()
    {
        return [
            'id' => '1002',
            'name' => 'Han Solo',
            'friends' => ['1000', '1003', '2001'],
            'appearsIn' => [4, 5, 6],
        ];
    }

    protected function getLeiaOrgana()
    {
        return [
            'id' => '1003',
            'name' => 'Leia Organa',
            'friends' => ['1000', '1002', '2000', '2001'],
            'appearsIn' => [4, 5, 6],
            'homePlanet' => 'Alderaan',
        ];
    }

    protected function getWilhuffTarkin()
    {
        return [
            'id' => '1004',
            'name' => 'Wilhuff Tarkin',
            'friends' => ['1001'],
            'appearsIn' => [4],
        ];
    }


    protected function getThreePiO()
    {
        return [
            'id' => '2000',
            'name' => 'C-3PO',
            'friends' => ['1000', '1002', '1003', '2001'],
            'appearsIn' => [4, 5, 6],
            'primaryFunction' => 'Protocol',
        ];
    }

    protected function getArtoo()
    {
        return [
            'id' => '2001',
            'name' => 'R2-D2',
            'friends' => ['1000', '1002', '1003'],
            'appearsIn' => [4, 5, 6],
            'primaryFunction' => 'Astromech',
        ];
    }

    protected function getHumans()
    {
        return [
            '1000' => $this->getLukeSkyWalker(),
            '1001' => $this->getDarthVader(),
            '1002' => $this->getHanSolo(),
            '1003' => $this->getLeiaOrgana(),
            '1004' => $this->getWilhuffTarkin(),
        ];
    }

    protected function getDroids()
    {
        return [
            '2000' => $this->getThreePiO(),
            '2001' => $this->getArtoo(),
        ];
    }

    /**
     * Helper function to get a character by ID.
     *
     * @param string $id
     *
     * @return array|null
     */
    protected function getStarWarsCharacter($id)
    {
        // The original implementation returns a promise here. That obviously
        // doesn't make sense for PHP.
        $humans = $this->getHumans();
        if (isset($humans[$id])) {
            return $humans[$id];
        }

        $droids = $this->getDroids();
        if (isset($droids[$id])) {
            return $droids[$id];
        }

        return NULL;
    }

    /**
     * Allows us to query for a character's friends.
     *
     * @param string $character
     *
     * @return array
     */
    protected function getStarWarsFriends($character)
    {
        return array_map([$this, 'getStarWarsCharacter'], $character['friends']);
    }
}
