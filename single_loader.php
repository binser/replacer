<?php

$name = $argv[1] ?? '';

$clearName = clearName($name);
$type = getFormType($clearName);
$dosage = getDosage($clearName);
$releaseForm = getForm($clearName);
$shortName = getShortName($clearName, array_filter([$type, $dosage, $releaseForm]));

echo "name => {$name}\n";
echo "clearName => {$clearName}\n";
echo "type => {$type}\n";
echo "dosage => {$dosage}\n";
echo "releaseForm => {$releaseForm}\n";
echo "shortName => {$shortName}\n";

function clearName(string $name): string
{
    $replaces = [
        ["/\"/ui" => ""],
        ["/\+/ui" => " плюс "],
        ["/(\d+)\s*\bМЕ\b/u" => "$1 ед. "],
        ["/(г|л)\/(д|доз|дозу|доза)\b\.?/ui" => "$1 на дозу "],
        ["/\/\s*([\d\.\,]*)\s*(л|мл|г|грамм)/ui" => " на $1 $2 "],
        ["/(\d)(нг|мкг|мг|кг|г|л|мл|доз)\b/ui" => "$1 $2"],
        ["/\bг\b/ui" => "грамм"],
        ["/\bнг\b/ui" => "нанограмм"],
        ["/\bмкг\b/ui" => "микрограмм"],
        ["/\bмг\b/ui" => "миллиграмм"],
        ["/\bкг\b/ui" => "килограмм"],
        ["/(\d+,\d+) (нано|микро|милли|кило)?грамм\b/ui" => "$1_$2грамма "],
        ["/((?:[02-9]|\b)[2-4]) (нано|микро|милли|кило)?грамм\b/ui" => "$1_$2грамма "],
        ["/(\d+) (нано|микро|милли|кило)?грамм\b/ui" => "$1 $2грамм "],
        ["/\bл\b/ui" => "литр"],
        ["/\bмл\b/ui" => "миллилитр"],
        ["/(\d+,\d+) (милли)?литр\b/ui" => "$1_$2литра "],
        ["/((?:[02-9]|\b)[2-4]) (милли)?литр\b/ui" => "$1_$2литра "],
        ["/((?:[02-9]|\b)1) (милли)?литр\b/ui" => "$1_$2литр "],
        ["/(\d+) (милли)?литр\b/ui" => "$1 $2литров "],
        ["/(\d)_/" => "$1 "],
        ["/(\d*[2-4])\s*ед\b\.?/ui" => " $1 единицы "],
        ["/(\d*[02-9][1]\b|\b1)\s*ед\b\.?/ui" => ", $1 единица "],
        ["/(\d+|\b)ед\b\.?/ui" => " $1 единиц "],
        ["/(\d+)([а-яё]+)/ui" => " $1 $2 "],
        ["/№\s*((?:\d*[02-9])*[2-4])\b/ui" => ", $1 штуки в упаковке, "],
        ["/№\s*(\d*[02-9][1]\b|\b1\b)/ui" => ", $1 штука в упаковке, "],
        ["/№\s*(\d+)/ui" => ", $1 штук в упаковке, "],
        ["/\(\d*\s*табл?\b\.?\)/ui" => ""],
        ["/\bтабл?\.?(\s*дисперг\.?)*/ui" => " таблетки "],
        ["/\b(капс|капсулы)\b\.?/ui" => " капсулы "],
        ["/\b(капл?\.?|капли)(\s*(глазные|глаз\.|гл\.))*/ui" => " капли "],
        ["/\b(гранул|гран)\b\.?/ui" => " гранулы "],
        ["/\bтест\-пол\b\.?/ui" => " тест-полоски "],
        ["/\bДозатор (инсул|инс)\b\.?/ui" => "Дозатор инсулиновый "],
        ["/\b(аэрозоль|аэрозол|аэроз|аэр)\b\.?\s*(д\.?\/инг\b\.?)?/ui" => " аэрозоль "],
        ["/\b(лиоф-т|лиоф)\b\.?/ui" => " лиофилизат "],
        ["/\b(дет|детский)\b\.?/ui" => " детский "],
        ["/\b(п\/о)\b\.?/ui" => ""],
        ["/\(*витамин\s*[A-Za-zА-Яа-я]\)*/ui" => ""],
        ["/\b(д\/|для )\s*\b(инфуз[а-яё]*|инф|рассас)\b\.?/ui" => ""],
        ["/\b(д\/|для )\s*\b(пр\/|при[её]м[а-яё]*)\b\.?\s*\b(внутрь|внутр|внут|вн)\b\.?(\s+и\b\s+(ингаляций|ингал|инг).?)?/ui" => ""],
        ["/\b(д\.?\/|для\b)?\s*(приготов|пригот|приг|пр|риг)?\.?\s*\b(р-ра|раст\b\.?|раств\b\.?|внут\b\.?|раствора|сусп[а-яё]*\.?)(\s*в\/в)?(\s*\/?(инфуз[а-яё]*|инф)\b\.?)?/ui" => ""],
        ["/\b(д\.?\/\s?|\/|для )*(в\/в|в\/м и п\/к|п\/к|подкожного|подкожн|подкож|подк)(\.|\s*)((введения|введен|введ|вв)\b\.?)?/ui" => ""],
        ["/\b(д\.?\/|для\b)\s*(нар|наруж|наружнего)\.?\s*\b(пр-я|пр\.?|применения\.?)\b/ui" => ""],
        ["/\b(флакон|флак|фл)\b\.?(\s*с\s+(раст|раств|раствор|растворит)\b\.?)?/ui" => ""],
        ["/\b(флаконы?|флакон|флак|фл)\b\.?/ui" => ""],
        ["/\bр-р\b\.?/ui" => " раствор "],
        ["/\b(пор|порош)\b\.?/ui" => " порошок "],
        ["/\b(сусп)\b\.?/ui" => " суспензия "],
        ["/\b(спр[еэ]й)\s+(назальн[а-яё]*|назал|наз)\b\.?/ui" => " спрей назальный "],
        ["/\b(конц-т|конц)\b\.?/ui" => " концентрат "],
        ["/\bд\/(ингал|инг|ин).*(\.|,|$)/uiU" => ""],
        ["/\bд\/приг.*(\.|,|$)/uiU" => ""],
        ["/\b(пл|плен|пленоч|пленочн|пленочной)\b\.?/ui" => ""],
        ["/\b(п|покр|покрыт|покрыты|покрытые)\b\.?/ui" => ""],
        ["/\b(о|об|обол|оболочкой|оболочка)\b\.?/ui" => ""],
        ["/(\bс\b\s+)?\b(модифиц[а-яё]*|модиф|мод)\b\.?/ui" => ""],
        ["/(\bс\b\s+)?\b(пролонг[а-яё]*|пролон|прол)\b\.?/ui" => ""],
        ["/\b(д-я|действ[а-яё]*|дейст)\b\.?/ui" => ""],
        ["/\b(высвобож[а-яё]*|высвоб|высв)\b\.?/ui" => ""],
        ["/\bдля\b(.+\.|,|$)/uiU" => ""],
        ["/(\d+(,\d+)?)\s*доз\b\.?/ui" => "$1_доз"],
        ["/\b(дозированный|дозирован|дозиров|дозир|доз)\b\.?/ui" => ""],
        ["/(\d+(,\d+)?)_/ui" => "$1 "],
        ["/\b(кишечнорас[а-яё]*|киш\.?\/рас[а-яё]*)\b\.?/ui" => ""],
        ["/\b(жевательн|жевател|жеват|жев)\b\.?/ui" => ""],
        ["/\bB(\d+)\b/" => "Б$1"],
        ["/\bD(\d+)\b/" => "Д$1"],
        ["/\bC(\d+)\b/" => "Ц$1"],
        ["/\sМЕ\s/ui" => " единиц "],
        ["/\sмлнМЕ\s/ui" => " миллиона единиц "],
        ["/\sтысМЕ\s/ui" => " тысяч единиц "],
        ["/\bКЕД\b/ui" => " единиц коагуляции "],
        ["/\-?(\d+(,\d+)?)\s*(нано|микро|милли|кило)?грамм\s+пак\b\.?/ui" => ", $1 $2грамм в пакете "],
        ["/карт.в руч.БиоматикПен2/ui" => ""],
        ["/плюс\s*р\-ль/ui" => ""],
        ["/сист\. Flash мон\. глюк\./ui" => ""],
        ["/\s+/ui" => " "],
        ["/\(+/ui" => "("],
        ["/\)+/ui" => ")"],
        ["/\(\s*\)/ui" => ""],
        ["/\s+(,|\.)/ui" => "$1"],
        ["/^[\s,]+(.+)/ui" => "$1"],
        ["/,\s+\)\s*$/ui" => ""],
        ["/(\s)+/ui" => "$1"],
        ["/(.+)[\s,\.,-,\\\,\/]+$/uiU" => "$1"],
        ["/,+/" => ","],
        ["/\s+\/\s+/" => " "]
    ];

    foreach ($replaces as $replace) {
        $name = preg_replace(array_key_first($replace), $replace[array_key_first($replace)], $name);
    }
    return trim($name);
}

function getDosage(string $name): ?string
{
    $regexp = "/(((?:\d+,)*(?:\d+)\s*(?:нано|микро|милли|кило)?(?:грамм|литр|единиц)(?:а|ов)?(?:\s*на\s((?:\d+,)*(?:\d+))*\s*(?:нано|микро|милли|кило)?(?:грамм|литр|единиц|дозу)(?:а|ов)?)?)(?:\s*плюс\s*((?:\d+,)*(?:\d+)\s*(?:нано|микро|милли|кило)?(?:грамм|литр|единиц)(?:а|ов)?(?:\s*на\s((?:\d+,)*(?:\d+))*\s*(?:нано|микро|милли|кило)?(?:грамм|литр|единиц|дозу)(?:а|ов)?)?))?)/ui";
    if (preg_match($regexp, $name, $matches)) {
        return $matches[1] ?? null;
    }
    return null;
}

function getFormType(string $name): ?string
{
    $regexp = "/\b(таблетки|капсулы|гранулы|аэрозоль|раствор|порошок|суспензия|спрей назальный|сироп|крем|пластырь|спрей|гель|капли)\b/ui";
    if (preg_match($regexp, $name, $matches)) {
        return $matches[1] ?? null;
    }
    return null;
}

function getForm(string $name): ?string
{
    $regexp = "/(\d+\s*штук[а-я]* в упаковке)/ui";
    if (preg_match($regexp, $name, $matches)) {
        return $matches[1] ?? null;
    }
    return null;
}

function getShortName(string $name, array $clear): ?string
{
    $name = str_replace($clear, '', $name);
    $replaces = [
        ["/\s+/ui" => " "],
        ["/\(+/ui" => "("],
        ["/\)+/ui" => ")"],
        ["/\(\s*\)/ui" => ""],
        ["/\s+(,|\.)/ui" => "$1"],
        ["/^[\s,]+(.+)/ui" => "$1"],
        ["/,\s+\)\s*$/ui" => ""],
        ["/(.+)[\s,\.,-,\\\,\/]+$/uiU" => "$1"],
        ["/,+/" => ","],
        ["/(\s)+/ui" => "$1"],
    ];
    foreach ($replaces as $replace) {
        $name = preg_replace(array_key_first($replace), $replace[array_key_first($replace)], $name);
    }
    return trim($name);
}
