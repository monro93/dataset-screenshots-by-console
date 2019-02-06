<?php

const MAIN_URL = "https://en.wikipedia.org/wiki/Category:Screenshots_of_video_games";
const DOMAIN_NAME = "https://en.wikipedia.org";
const FOLDER = "dataset";
const VERBOSE = true;

$platformsToParse = [
    "Amiga",
    "Amstrad CPC",
    "Android",
    "Apple II",
    "Atari",
    "DOS",
    "Dreamcast",
    "Game Boy",
    "Game Boy Advance",
    "Game Boy Color",
    "Sega Game Gear",
    "GameCube",
    "iOS",
    "Master System",
    "Neo Geo",
    "Nintendo 3DS",
    "Nintendo 64",
    "Nintendo DS",
    "Nintendo Entertainment System",
    "Nintendo Switch",
    "PlayStation (console)",
    "PlayStation 2",
    "PlayStation 3",
    "PlayStation 4",
    "PlayStation Portable",
    "PlayStation Vita",
    "Super Nintendo Entertainment System",
    "Wii",
    "Wii U",
    "Xbox",
    "Xbox 360",
    "Xbox One",
    "ZX Spectrum"
];


if(!file_exists(FOLDER)) {
    mkdir(FOLDER, 0777);
}


writeln("Starting parser");
parseCategories(MAIN_URL, $platformsToParse);

function parseCategories($url, $platformsToParse) {
    $html = file_get_contents($url);
    if(preg_match_all('|<a class="CategoryTreeLabel[^"]*"\shref="([^"]*)"\s*>([^<]*)|i', $html, $matches)) {
        writeln(count($matches[0])." categories found.");
        foreach ($matches[0] as $k => $match) {
            $categoryName = normalizeCategoryString($matches[2][$k]);
            if(in_array($categoryName, $platformsToParse)){
                writeln("====");
                writeln("Parsing category: ".$categoryName."");
                $url = DOMAIN_NAME.$matches[1][$k];
                parseCategory($url, $categoryName);
            }
        }
    }else {
        writeln("Categories not found");
    }
}

function parseCategory($url, $categoryName) {
    $html = file_get_contents($url);
    if(preg_match_all('|<a href="(/wiki/File:([^"]*))"\s*title|i', $html, $matches)) {
        foreach ($matches[0] as $j => $match) {
            $imageName = $matches[2][$j];
            writeln("Found image: ".$imageName."");
            $url = DOMAIN_NAME.'/'.$matches[1][$j];
            parseImagePage($url, $categoryName, $imageName);
        }
    }else{
        writeln("Didn't find images");
    }
}

function parseImagePage($url, $categoryName, $imageName) {
    $html3 = file_get_contents($url);
    if(preg_match('|<img\s*alt="[^"]*"\s*src="([^"]*)"|i', $html3, $matches)) {
        saveImage(
            FOLDER.DIRECTORY_SEPARATOR.$categoryName,
            $imageName,
            'https:'.$matches[1]
        );
    }else {
        writeln("Cannot download image");
    }
}

function saveImage($folder, $fileName, $url) {
    $savingFileName = $folder.DIRECTORY_SEPARATOR.$fileName;
    writeln("Saving image: ".$savingFileName."");
    if(!file_exists($folder)) {
        mkdir($folder, 0777);
    }
    file_put_contents($savingFileName, file_get_contents($url));
}

function normalizeCategoryString($string) {
    return trim(str_replace(['Screenshots of', 'games'], '', $string));
}

function writeln($message) {
    if(VERBOSE) {
        echo $message."\n";
    }
}