<?php

use DanAbrey\ESPNNCAAStatsScraper\Crawler\PlayerCrawler;
use DanAbrey\ESPNNCAAStatsScraper\PlayerSeason;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class PlayerCrawlerTest extends TestCase
{
    /**
     * @var PlayerCrawler
     */
    private $playerCrawler;
    /**
     * @var string
     */
    private $htmlResponse;

    protected function setUp(): void
    {
        $this->playerCrawler = new PlayerCrawler();
    }

    public function players()
    {
        return [
            [
                file_get_contents('./tests/fixtures/jerry-jeudy.html'),
                'Jerry Jeudy',
                'WR',
                'Alabama Crimson Tide',
                [2017, 2018, 2019],
            ],
            [
                file_get_contents('./tests/fixtures/johnathan-bennett.html'),
                'Johnathan Bennett',
                'QB',
                'Liberty Flames',
                [2019],
            ],
            [
                file_get_contents('./tests/fixtures/tua-tagovailoa.html'),
                'Tua Tagovailoa',
                'QB',
                'Alabama Crimson Tide',
                [2017, 2018, 2019],
            ],
        ];
    }

    /**
     * @dataProvider players
     * @param $html
     * @param $name
     * @param $position
     * @param $college
     * @param $seasons
     */
    public function test_can_extract_name($html, $name, $position, $college, $seasons)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $this->assertEquals($name, $this->playerCrawler->extractPlayerName($crawler));
    }

    /**
     * @dataProvider players
     * @param $html
     * @param $name
     * @param $position
     * @param $college
     * @param $seasons
     */
    public function test_can_extract_position($html, $name, $position, $college, $seasons)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $this->assertEquals($position, $this->playerCrawler->extractPlayerPosition($crawler));
    }

    /**
     * @dataProvider players
     * @param $html
     * @param $name
     * @param $position
     * @param $college
     * @param $seasons
     */
    public function test_can_extract_college($html, $name, $position, $college, $seasons)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $this->assertEquals($college, $this->playerCrawler->extractPlayerCollege($crawler));
    }

    /**
     * @dataProvider players
     * @param $html
     * @param $name
     * @param $position
     * @param $college
     * @param $seasons
     */
    public function test_can_extract_seasons($html, $name, $position, $college, $seasons)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        /** @var PlayerSeason[] $crawledSeasons */
        $crawledSeasons = $this->playerCrawler->buildSeasonDataFromCrawler($crawler);
        $this->assertIsArray($crawledSeasons);

        foreach($seasons as $season) {
            $this->assertArrayHasKey($season, $crawledSeasons);
            $this->assertInstanceOf(PlayerSeason::class, $crawledSeasons[$season]);
        }
    }

    public function test_can_extract_season_data_wr()
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent(file_get_contents('./tests/fixtures/jerry-jeudy.html'));
        /** @var PlayerSeason[] $crawledSeasons */
        $crawledSeasons = $this->playerCrawler->buildSeasonDataFromCrawler($crawler);

        $this->assertSame(2019, $crawledSeasons[2019]->getYear());
        $this->assertSame('ALA', $crawledSeasons[2019]->getTeam());
        $this->assertSame(64, $crawledSeasons[2019]->getReceptions());
        $this->assertSame(867, $crawledSeasons[2019]->getReceivingYards());
    }

    public function test_can_extract_season_data_qb()
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent(file_get_contents('./tests/fixtures/tua-tagovailoa.html'));
        /** @var PlayerSeason[] $crawledSeasons */
        $crawledSeasons = $this->playerCrawler->buildSeasonDataFromCrawler($crawler);

        $this->assertSame(2019, $crawledSeasons[2019]->getYear());
        $this->assertSame('ALA', $crawledSeasons[2019]->getTeam());
        $this->assertSame(33, $crawledSeasons[2019]->getPassingTouchdowns());
        $this->assertSame(17, $crawledSeasons[2019]->getRushingYards());
    }

    public function throws_exception_when_player_not_found()
    {
        // Placeholder for test to be created when exception implemented
        $this->assertEquals(true, true);
    }
}
