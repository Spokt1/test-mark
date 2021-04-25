<?php


namespace App\Classes;


class Mark
{
    private $boldRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*+[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*+_)+?)__(?!_)/us',
    );

    private $cursiveRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    private $markers = '*_';


    public function parse(string $str)
    {
        return $this->elements($this->prepareElements(trim($str)));
    }

    /**
     * Функция для разбора входной строки
     *
     * @param string $text
     * @return array
     */

    private function prepareElements($text): array
    {
        $elements = [];

        /* ищем сивмолы _* в тексте */
        while ($convertString = strpbrk($text, $this->markers)) {

            $markerPosition = strlen($text) - strlen($convertString);

            $element = isset($convertString[1]) ? $this->checkRegexp($convertString) : null;

            if (is_null($element)) {
                break;
            }

            /*Забираем все что было то тега*/
            $unmarkedText = substr($text, 0, $markerPosition);
            $elements[] = ['text' => $unmarkedText];

            /*Добавляем в массив то что надо заменить*/
            $elements[] = $element['element'];

            /*отрезаем забранный кусок строки */
            $text = substr($text, $markerPosition + $element['length']);

        }

        /*на случай если нет нужных нам тегов или пустая строка*/
        $elements[] = ['text' => $text];

        return $elements;
    }


    /**
     * Проверяем строку на соответствие регулярному выражение и возвращаем массив вида
     * length => Длина строки с тегами
     * element:[
     *  tag => тег,
     *  text => текст внутри тегов
     * ]
     * @param string $convertString
     * @return array|void
     */

    private function checkRegexp(string $convertString)
    {
        $marker = $convertString[0];
        if ($convertString[1] === $marker && preg_match($this->boldRegex[$marker], $convertString, $matches)) {
            $tag = 'strong';
        } elseif (preg_match($this->cursiveRegex[$marker], $convertString, $matches)) {
            $tag = 'em';
        } else {
            return;
        }
        return [
            'length' => strlen($matches[0]),
            'element' => ['tag' => $tag, 'text' => $matches[1]]
        ];
    }

    /**
     * Делаем что то на подобии рекурсии, будем вызывать функцию
     * проверки на существование тега если указан ключ tag
     * @param array $element
     * @return array
     */
    private function checkRecursive(array $element): array
    {
        if (key_exists('tag', $element)) {
            $element['elements'] = $this->prepareElements($element['text']);
        }
        return $element;
    }

    /**
     * @param array $elements
     * @return string
     */
    private function elements(array $elements): string
    {
        $strMarkup = '';
        foreach ($elements as $element) {
            $strMarkup .= '' . $this->element($element);
        }

        return $strMarkup;
    }

    /**
     * Построение нашей строки с учетом тегов
     * @param array $element
     * @return string
     */
    private function element(array $element): string
    {
        $element = $this->checkRecursive($element);

        $hasTag = key_exists('tag',$element);

        $strMarkup = '';

        $text = $element['text'];

        if($hasTag){
            $strMarkup .= '<' . $element['tag'];
        }

        $hasContent = isset($text) || isset($element['elements']);

        if ($hasContent)
        {
            $strMarkup .= $hasTag ? '>' : '';

            if (isset($element['elements'])) {
                $strMarkup .= $this->elements($element['elements']);
            } else {
                $strMarkup .= $text;
            }

            $strMarkup .= $hasTag ? '</' . $element['tag'] . '>' : '';
        }
        elseif ($hasTag) {
            $strMarkup .= ' />';
        }

        return $strMarkup;
    }
}