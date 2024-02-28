CREATE TABLE `crossword_words` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `word` varchar(255) NOT NULL,
                                   `clue` text NOT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crosswords` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `title` varchar(255) NOT NULL,
                              `slug` varchar(255) NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `crossword_word_relationships` (
                                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                                `crossword_id` int(11) NOT NULL,
                                                `word_id` int(11) NOT NULL,
                                                `position_x` int(11) NOT NULL,
                                                `position_y` int(11) NOT NULL,
                                                `orientation` varchar(255) NOT NULL,
                                                PRIMARY KEY (`id`),
                                                FOREIGN KEY (`crossword_id`) REFERENCES `crosswords`(`id`) ON DELETE CASCADE,
                                                FOREIGN KEY (`word_id`) REFERENCES `crossword_words`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
