<?php

function getPDOConnection($host, $dbname, $username, $password)
{
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, $username, $password, $options);
}

function insertWord($pdo, $word, $clue)
{
    $sql = "INSERT INTO crossword_words (word, clue) VALUES (:word, :clue)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':word' => $word, ':clue' => $clue]);
    return $pdo->lastInsertId();
}

function insertCrossword($pdo, $title, $slug)
{
    $sql = "INSERT INTO crosswords (title, slug) VALUES (:title, :slug)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':title' => $title, ':slug' => $slug]);
    return $pdo->lastInsertId();
}

function insertRelationship($pdo, $crosswordId, $wordId, $positionX, $positionY, $orientation)
{
    $sql = "INSERT INTO crossword_word_relationships (crossword_id, word_id, position_x, position_y, orientation) VALUES (:crossword_id, :word_id, :position_x, :position_y, :orientation)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':crossword_id' => $crosswordId,
        ':word_id' => $wordId,
        ':position_x' => $positionX,
        ':position_y' => $positionY,
        ':orientation' => $orientation
    ]);
}
