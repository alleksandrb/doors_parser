<?php
//url сайта изменен

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/funcs.php';

use Treinetic\ImageArtist\lib\Commons\Node;
use Treinetic\ImageArtist\lib\Image;
use Treinetic\ImageArtist\lib\Shapes\PolygonShape;

set_time_limit(0); //нет ограничения выполнения скрипта по времени
ini_set('memory_limit', -1); //нет ограничения по памяти

echo 'start parsing...';

$name_collection = 'avrora';

mkdir(__DIR__ . 'index2.php/' . $name_collection);

$json = file_get_contents(__DIR__ . '/product.txt');
$products = json_decode($json);
$json = file_get_contents(__DIR__ . '/photos.txt');
$photos = json_decode($json);

$product = $products->{$name_collection};
$colors_group = $product->colors_group;

$portal_links = [];


foreach ($colors_group as $colors_item) {
    foreach ($colors_item->colors as $name_colors) {
        $portal_links[$name_colors] = 'https://doors.ru/construct/images/' . $name_collection . '/portals/' . 'Modo' . '_' . $name_colors . '.png';
    }
}


$photo = $photos->{$name_collection};
$doors_photo_links = [];

foreach ($photo->doors as $door_name => $door_links) {
    foreach ($door_links as $door_link) {
        if (is_string($door_link))
            $doors_photo_links[$door_name][substr($door_link, 0, strlen($door_link) - 4)] = 'https://doors.ru/construct/images/' . $name_collection . '/doors/' . $door_name . '/' . $door_link;
        if (is_array($door_link)) {
            foreach ($door_link as $glass) {
                $doors_photo_links[$door_name]['glasses'][$glass] = 'https://doors.ru/construct/images/' . $name_collection . '/doors/' . $door_name . '/glasses/' . $glass;
            }
        }
    }
}

$path_doors_with_glasses = [];
$i = 1;
foreach ($doors_photo_links as $door_model => $elem) {
    if (isset($elem['glasses'])) {
        foreach ($elem['glasses'] as $glass_name => $glass) {
            foreach ($elem as $door_name => $one_door) {
                if (is_string($one_door)) {
                    foreach ($portal_links as $portal_name => $portal_link) {
                        if (substr(stristr($door_name, '_'), 1) === $portal_name)
                            $portal = new Image($portal_link);
                    }
                    sleep(1);
                    echo '..' . $i++ . PHP_EOL;

                    $door = new Image($one_door);
                    $glass = new Image($glass);
                    $door->merge($glass, 0, 0);
                    $door->merge($portal, 0, 0);
                    $door->save(__DIR__ . '/' . $door_name . '_' . $glass_name);
                    $door = new PolygonShape(__DIR__ . '/' . $door_name . '_' . $glass_name);
                    $door->push(new Node(9, 6, Node::$PERCENTAGE_METRICS));
                    $door->push(new Node(8, 97, Node::$PERCENTAGE_METRICS));
                    $door->push(new Node(92, 97, Node::$PERCENTAGE_METRICS));
                    $door->push(new Node(92, 6, Node::$PERCENTAGE_METRICS));
                    $door->build();
                    $door->save(__DIR__ . '/' . $door_name . '_' . $glass_name);
                    $path_doors_with_glasses[$door_name][] = $door_name . '_' . $glass_name;
                }
            }
        }
    } else {
        foreach ($elem as $door_name => $link) {
            foreach ($portal_links as $portal_name => $portal_link) {
                if (substr(stristr($door_name, '_'), 1) === $portal_name)
                    $portal = new Image($portal_link);
            }
            sleep(1);
            echo '..' . $i++ . PHP_EOL;
            $door = new Image($link);
            $door->merge($portal, 0, 0);
            $door->save(__DIR__ . '/' . $door_name . '.png');
            $door = new PolygonShape(__DIR__ . '/' . $door_name . '.png');
            $door->push(new Node(8, 6, Node::$PERCENTAGE_METRICS));
            $door->push(new Node(8, 97, Node::$PERCENTAGE_METRICS));
            $door->push(new Node(92, 97, Node::$PERCENTAGE_METRICS));
            $door->push(new Node(92, 6, Node::$PERCENTAGE_METRICS));
            $door->build();
            $door->save(__DIR__ . '/' . $door_name . '.png');
            $path_doors_with_glasses[$door_name][] = $door_name . '.png';
        }
    }
}

$path_doors_with_glasses = json_encode($path_doors_with_glasses);
file_put_contents(__DIR__ . '/json_path/' . $name_collection . '.json', $path_doors_with_glasses);

echo 'end parsing';
