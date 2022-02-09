<?php

declare(strict_types=1);


namespace App\Services;


class WordGuesserService
{
    /**
     * @var string
     */
    private $pathToDic;

    private int $patternLength;

    public function __construct(string $pathToDic)
    {
        $this->pathToDic = $pathToDic;
    }

    public function guess(string $pattern, array $included, array $excluded, array $misplacedLetters = null): array
    {
        if (!is_file($this->pathToDic)) {
            throw new \Exception('File not found');
        }

        if ($included === [""]) {
            $included = [];
        }

        if ($excluded === [""]) {
            $excluded = [];
        }

        $this->patternLength = strlen(str_replace('-', '', $pattern));

        preg_match_all('/-(?P<includes>[a-zA-Z])/', $pattern, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $included[] = $match['includes'];
        }

        if ($misplacedLetters) {
            foreach (array_keys($misplacedLetters) as $misplacedLetter) {
                $included[] = $misplacedLetter;
            }
        }

        $words = [];
        foreach (file($this->pathToDic) as $word) {
            $word = trim($word);

            if (strlen($word) !== $this->patternLength) {
                continue;
            }

            if (false === $this->containsMisplacedLetters($word, $misplacedLetters)
                && false !== ($letters = $this->wordMatchesThePattern($word, $pattern))
                && $this->array_intersect($included, $letters) === $included
                && $this->array_intersect($excluded, $this->array_diff($letters, $included)) === []
            ) {
                $words[] = $word;
            }
        }
        return $words;
    }

    /**
     * My own array_intersect method that handles arrays with duplicate values in $array1
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function array_intersect(array $array1, array $array2): array
    {
        $result = [];
        foreach ($array1 as $val) {
            if (($key = array_search($val, $array2, TRUE))!==false) {
                $result[] = $val;
                unset($array2[$key]);
            }
        }
        return $result;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function array_diff(array $array1, array $array2): array
    {
        $result = [];
        foreach ($array1 as $val) {
            if (($key = array_search($val, $array2, TRUE))!==false) {
                unset($array2[$key]);
            } else {
                $result[] = $val;
            }
        }
        return $result;
    }

    private function containsMisplacedLetters(string $word, ?array $misplacedLetters): bool
    {
        if (null === $misplacedLetters) {
            return false;
        }

        foreach ($misplacedLetters as $letter => $positions) {
            foreach ($positions as $position) {
                if ($word[$position] === $letter) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $word
     * @param string $pattern
     * @return array|false
     */
    private function wordMatchesThePattern(string $word, string $pattern)
    {
        $letters = [];
        for($i = 0, $j = 0, $len = strlen($word); $i < $len; $i++, $j++) {
            if ($pattern[$j] === '-') {
                if ($word[$i] === $pattern[$j+1]) {
                    return false;
                }
                $letters[] = $word[$i];
                $j++;
                continue;
            }
            if ($pattern[$j] !== '.' && $word[$i] !== $pattern[$j]) {
                return false;
            }
            if ($pattern[$j] === '.') {
                $letters[] = $word[$i];
            }
        }

        return $letters;
    }
}
