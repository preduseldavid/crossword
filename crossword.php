<?php

include 'crossword.php';

$jsonContent = file_get_contents('words.json');
$wordDatabase = json_decode($jsonContent, true);
shuffle($wordDatabase);

$board = [
    ['#', '#', '#', '#', '#'],
    ['#', '#', '#', '#', '#'],
    ['#', '#', '#', '#', '#'],
    ['#', '#', '#', '#', '#'],
    ['#', '#', '#', '#', '#'],
];

function findStartingCells($board)
{
    $startingCells = [];
    for ($i = 0; $i < count($board); $i++) {
        for ($j = 0; $j < count($board[$i]); $j++) {
            if ($board[$i][$j] == '#' || $board[$i][$j] == '%') {
                $startingCells[] = ['start' => [$i, $j], 'direction' => 'horizontal']; // Assuming horizontal direction
                $startingCells[] = ['start' => [$i, $j], 'direction' => 'vertical']; // Assuming vertical direction
            }
        }
    }
    return $startingCells;
}

function wordFits($word, $startCell, $direction, $board)
{
    $startRow = $startCell[0];
    $startCol = $startCell[1];
    $wordLength = strlen($word);

    if ($direction == 'horizontal') {
        if ($startCol + $wordLength > count($board[0])) {
            return false;
        }
        for ($i = 0; $i < $wordLength; $i++) {
            $next = $board[$startRow][$startCol + $i];
            if ($next != '-' && $next != $word[$i] && $next != '#' && $next != '%') {
                return false;
            }
        }
    } else { // vertical
        if ($startRow + $wordLength > count($board)) {
            return false;
        }
        for ($i = 0; $i < $wordLength; $i++) {
            $next = $board[$startRow + $i][$startCol];
            if ($next != '-' && $next != $word[$i] && $next != '#' && $next != '%') {
                return false;
            }
        }
    }

    return true;
}

function addWordToBoard(&$board, $word, $startCell, $direction)
{
    $startRow = $startCell[0];
    $startCol = $startCell[1];
    $wordLength = strlen($word);

    if ($direction == 'horizontal') {
        for ($i = 0; $i < $wordLength; $i++) {
            $board[$startRow][$startCol + $i] = $word[$i];
        }
    } else { // vertical
        for ($i = 0; $i < $wordLength; $i++) {
            $board[$startRow + $i][$startCol] = $word[$i];
        }
    }
}

function removeWordFromBoard(&$board, $word, $startCell, $direction)
{
    $startRow = $startCell[0];
    $startCol = $startCell[1];
    $wordLength = strlen($word);

    if ($direction == 'horizontal') {
        for ($i = 0; $i < $wordLength; $i++) {
            $board[$startRow][$startCol + $i] = ($board[$startRow][$startCol + $i] == $word[$i]) ? '-' : '#';
        }
    } else { // vertical
        for ($i = 0; $i < $wordLength; $i++) {
            $board[$startRow + $i][$startCol] = ($board[$startRow + $i][$startCol] == $word[$i]) ? '-' : '#';
        }
    }
}

function generateCrossword(&$board, $wordDatabase, $usedWords = [], $cellIndex = 0)
{
    $startingCells = findStartingCells($board);

    // Base case: if all cells have been processed, return true and the starting cells
    if ($cellIndex >= count($startingCells)) {
        return [true, $usedWords];
    }

    $cell = $startingCells[$cellIndex];
    foreach ($wordDatabase as $word) {
        if (!in_array($word, array_column($usedWords, 'word')) && wordFits($word, $cell['start'], $cell['direction'],
                $board)) {
            addWordToBoard($board, $word, $cell['start'], $cell['direction']);
            $usedWords[] = [
                // the clue should have another value here, but it takes too much effort to generate the clues for each word. In a real world example this should be replaced
                'word' => $word, 'clue' => $word, 'start' => $cell['start'], 'direction' => $cell['direction']
            ];

            // Recursively try to fill the rest of the board
            $result = generateCrossword($board, $wordDatabase, $usedWords, $cellIndex + 1);
            if ($result[0]) {
                return $result;
            }

            // If no solution was found, remove the word from the board (backtrack) and continue with the next word
            removeWordFromBoard($board, $word, $cell['start'], $cell['direction']);
            array_pop($usedWords);
        }
    }

    // If no word can be placed in the current starting cell, move to the next cell
    return generateCrossword($board, $wordDatabase, $usedWords, $cellIndex + 1);
}

function displayCrossword($board)
{
    foreach ($board as $row) {
        foreach ($row as $cell) {
            echo $cell.' ';
        }
        echo "\n";
    }
    echo "\n";
}

function displayUsedWords($usedWords)
{
    foreach ($usedWords as $wordInfo) {
        echo "Word: ".$wordInfo['word']."\n";
        echo "Start Cell: (".$wordInfo['start'][0].", ".$wordInfo['start'][1].")\n";
        echo "Direction: ".$wordInfo['direction']."\n";
        echo "\n";
    }
}


list($allFilled, $usedWords) = generateCrossword($board, $wordDatabase);
if ($allFilled) {
    echo "Crossword generated successfully.\n";
} else {
    echo "Unable to fill the entire board.\n";
}

displayCrossword($board);
displayUsedWords($usedWords);

$pdo = getPDOConnection('127.0.0.1', 'crossover', 'docker_user', 'password');
$crosswordId = insertCrossword($pdo, 'My Crossword', 'my-crossword');

// Insert the crossword into the crosswords table
$crosswordId = insertCrossword($pdo, 'History '.rand(), 'Crossword about history - '.rand());

foreach ($usedWords as $wordInfo) {
    // Check if the word already exists in the words table
    $stmt = $pdo->prepare("SELECT id FROM crossword_words WHERE word = ?");
    $stmt->execute([$wordInfo['word']]);
    $wordId = $stmt->fetchColumn();

    // If the word doesn't exist, insert it
    if (!$wordId) {
        $wordId = insertWord($pdo, $wordInfo['word'], $wordInfo['clue']);
    }

    // Insert the word into the crossword_words table
    insertRelationship($pdo, $crosswordId, $wordId, $wordInfo['start'][0], $wordInfo['start'][1],
        $wordInfo['direction']);
}
