<?php
namespace DanAbrey\ESPNNCAAStatsScraper\Crawler;

use DanAbrey\ESPNNCAAStatsScraper\PlayerSeason;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PlayerCrawler
{
    public const POSITIONS_MAP = [
        'Quarterback' => 'QB',
        'Running Back' => 'RB',
        'Wide Receiver' => 'WR',
        'Tight End' => 'TE',
    ];

    /**
     * Keys represent titles of data tables on CBS
     * Values represent related name of property on PlayerSeason DTO
     */
    public const DATATABLE_PARSER_MAP = [
        'Rushing' => [
            0 => 'rushingAttempts',
            1 => 'rushingYards',
            3 => 'rushingTouchdowns',
        ],
        'Receiving' => [
            0 => 'receptions',
            1 => 'receivingYards',
            3 => 'receivingTouchdowns',
        ],
        'Passing' => [
            0 => 'passingCompletions',
            1 => 'passingAttempts',
            3 => 'passingYards',
            5 => 'passingTouchdowns',
            6 => 'passingInterceptions',
            8 => 'sacked',
            9 => 'passerRating',
        ]
    ];

    public function buildSeasonDataFromCrawler(Crawler $crawler): array
    {
        $playerSeasons = [];
        $dataTables = $crawler->filter('.StatsTableWrapper section.ResponsiveTable');

        foreach ($dataTables as $dataTable) {
            $tableCrawler = new Crawler($dataTable);
            $title = $tableCrawler->filter('.Table__Title')->text();

            // Check that this particular table type can be parsed, by checking DATATABLE_PARSER_MAP
            if (!array_key_exists($title, self::DATATABLE_PARSER_MAP)) {
                continue;
            }

            /**
             * Set up season data from the 'identifier tables' - the first two columns of ESPN stats tables are
             * actually a separate table, housing just the years and college names
             */
            $seasonIdentifierRows = $tableCrawler->filter('.Table.Table--fixed-left tbody tr');

            $seasonsInThisTable = [];
            foreach ($seasonIdentifierRows as $seasonIdentifierRow) {
                $rowCrawler = new Crawler($seasonIdentifierRow);

                $year = $rowCrawler->filter('td')->first()->text();
                $college = $rowCrawler->filter('td')->last()->text();
                $playerSeasons[$year] = $playerSeasons[$year] ?? [];

                $playerSeasons[$year]['year'] = $year;
                $playerSeasons[$year]['team'] = $college;

                $seasonsInThisTable[] = $year;
            }

            $statsRows = $tableCrawler->filter('.Table__Scroller table tbody tr');


            for ($j = 0; $j < count($statsRows); $j++) {
                $statsRow = $statsRows->getNode($j);
                $rowCrawler = new Crawler($statsRow);
                $cells = $rowCrawler->filter('td');

                foreach (iterator_to_array($cells) as $i => $cell) {
                    // Check if DATATABLE_PARSER_MAP for this type of table has this key defined, skip if not
                    if (!array_key_exists($i, self::DATATABLE_PARSER_MAP[$title])) {
                        continue;
                    }

                    $playerSeasons[$seasonsInThisTable[$j]][self::DATATABLE_PARSER_MAP[$title][$i]] = str_replace(',', '', $cell->textContent);
                }
            }
        }


        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);

        foreach($playerSeasons as &$playerSeason) {
            $playerSeason = $serializer->denormalize($playerSeason, PlayerSeason::class);
        }

        return $playerSeasons;
    }

    public function extractPlayerName(Crawler $crawler): string
    {
        return join(' ',
            $crawler->filter('.PlayerHeader__Name span')->each(function(Crawler $el) { return $el->text(); })
        );
    }

    public function extractPlayerPosition(Crawler $crawler): string
    {
        return self::POSITIONS_MAP[$crawler->filter('.PlayerHeader__Team_Info li:nth-of-type(3)')->text()];
    }

    public function extractPlayerCollege(Crawler $crawler): string
    {
        return $crawler->filter('.PlayerHeader__Team_Info li:nth-of-type(1)')->text();
    }
}
