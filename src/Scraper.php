<?php
namespace DanAbrey\ESPNNCAAStatsScraper;

use DanAbrey\ESPNNCAAStatsScraper\Crawler\PlayerCrawler;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Scraper
{

    private const POSITIONS_MAP = [
        'Quarterback' => 'QB',
        'Running Back' => 'RB',
        'Wide Receiver' => 'WR',
        'Tight End' => 'TE',
    ];

    /**
     * Keys represent titles of data tables on CBS
     * Values represent related name of property on PlayerSeason DTO
     */
    private const DATATABLE_PARSER_MAP = [
        'Rushing' => [
            0 => 'team',
            1 => 'gamesPlayed',
            2 => 'rushingAttempts',
            3 => 'rushingYards',
            4 => 'rushingTouchdowns'
        ],
        'Receiving' => [
            0 => 'receptions',
            1 => 'receivingYards',
            3 => 'receivingTouchdowns'
        ],
        'Return' => [
            0 => 'team',
            1 => 'gamesPlayed',
            2 => 'puntReturns',
            3 => 'puntReturnYards',
            4 => 'puntReturnTouchdowns',
            5 => 'puntReturnLong',
            6 => 'kickoffReturns',
            7 => 'kickoffReturnYards',
            8 => 'kickoffReturnTouchdowns',
            9 => 'kickoffReturnLong',
        ],
        'Fumbles' => [
            0 => 'team',
            1 => 'gamesPlayed',
            2 => 'fumbles',
            3 => 'fumblesLost',
        ],
        'Passing Stats' => [
            0 => 'team',
            1 => 'gamesPlayed',
            2 => 'passingAttempts',
            3 => 'passingCompletions',
            4 => 'passingYards',
            5 => 'passingInterceptions',
            6 => 'passingTouchdowns',
            7 => 'sacked',
            8 => 'passerRating',
        ]
    ];

    /**
     * Given a player ID, scrape ESPN and return a PlayerStats object containing relevant data
     * If an invalid ID (page not found on ESPN), throw an exception
     * @param int $playerId
     * @return Player
     */
    public function getPlayerStats(int $playerId): Player
    {
        $url = sprintf('https://www.espn.co.uk/college-football/player/stats/_/id/%s', $playerId);

        $client = new Client();

        $crawler = $client->request('GET', $url);
        $player = new Player();
        $player->setId($playerId);

        $playerCrawler = new PlayerCrawler();

        $position = $playerCrawler->extractPlayerPosition($crawler);
        $player->setPosition($position);

        $name = $playerCrawler->extractPlayerName($crawler);
        $player->setName($name);

        $college = $playerCrawler->extractPlayerCollege($crawler);
        $player->setCollege($college);

        $seasons = $playerCrawler->buildSeasonDataFromCrawler($crawler);
        $player->setSeasons($seasons);

        return $player;
    }
}
