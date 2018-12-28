<?php

/**
 * Всомогательный класс Лист для создания дерефа Хаффмана
 * Class Leaf
 */
class Leaf
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}

/**
 * Вспомогательный класс Узел для построения дерева Хаффмана
 * Class Node
 */
class Node
{
    /**
     * Значение узла
     * @var integer
     */
    private $value;

    /**
     * Левая ветвь
     * @var Leaf|Node
     */
    private $left;

    /**
     * Правая ветвь
     * @var Leaf|Node
     */
    private $right;

    public function __construct(int $value, $left, $right)
    {
        $this->value = $value;
        $this->left = $left;
        $this->right = $right;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function getRight()
    {
        return $this->right;
    }
}

/**
 * Класс реализует сжатие и восстановление строки по алгоритму Хаффмана
 * Class Huffman
 */
class Huffman
{
    /**
     * Массив для преобразования из строки в дерево
     * @var array
     */
    private $data = [];

    /**
     * Ассоциативный массив кодирования, где ключ - буква, значение - двоичный код
     * @var array
     */
    private $codeTable = [];

    /**
     * Входная строка
     * @var string
     */
    private $realStr = '';


    /**
     * Метод формирует упорядоченый по убыванию массив объектов класса Leaf
     * @param string $str
     */
    private function makeList(string $str)
    {
        $this->realStr = $str;

        $symbolsArr = [];

        for($i = 0; $i < mb_strlen($str); $i++){
            $symbol = $str[$i];

            if(!isset($symbolsArr[$symbol])){
                $symbolsArr[$symbol] = 1;
            }else{
                $symbolsArr[$symbol] += 1;
            }
        }

        arsort($symbolsArr);

        foreach($symbolsArr as $key => $value){
            $this->data[] = new Leaf($key, $value);
        }
    }

    /**
     * Метод формирует из массива объектов класса Leaf бинарное дерево по алгоритму Хаффмана
     * @see makeList
     */
    private function createHuffmansTree()
    {
        while (count($this->data) > 2)
        {
            /**
             * @var $firstLeaf Leaf
             */
            $firstLeaf = array_pop($this->data);

            /**
             * @var $secondLeaf Leaf
             */
            $secondLeaf = array_pop($this->data);

            $leafsValueSum = $firstLeaf->getValue() + $secondLeaf->getValue();

            # временная узел
            $tmpNode = new Node($leafsValueSum, $secondLeaf, $firstLeaf);
            $tmpNodeValue = $tmpNode->getValue();

            # если значение нового узла больше, чем самое большое, вставляем на первую позицию
            # если меньше самого маленького, добавляем в конец
            # либо ищем место для вставки
            if($tmpNodeValue > $this->data[0]->getValue()){
                array_unshift($this->data, $tmpNode);
            }else if ($tmpNodeValue < $this->data[count($this->data) - 1]->getValue()){
                $this->data[] = $tmpNode;
            }else{
                for($i = 1; $i < count($this->data); $i++)
                {
                    if($this->data[$i - 1]->getValue() >= $tmpNodeValue
                        && $tmpNodeValue > $this->data[$i]->getValue()){
                        $this->data = array_merge(
                            array_slice($this->data, 0, $i),
                            array($tmpNode),
                            array_slice($this->data, $i)
                        );
                        break;
                    }
                }
            }
        }

        $this->data = new Node(
            $this->data[0]->getValue() + $this->data[1]->getValue(),
            $this->data[0],
            $this->data[1]
        );
    }

    /**
     * Рекурсивный обход дерева и построение табилцы кодирования
     * @param Node|Leaf|array $data
     * @param string $code
     */
    private function HuffmanRecursion($data, $code='')
    {
        if($data instanceof Node){
            $this->HuffmanRecursion($data->getLeft(), $code.'0');
            $this->HuffmanRecursion($data->getRight(), $code.'1');
        }else if($data instanceof Leaf){
            $this->codeTable[$data->getKey()] = $code;
        }
    }

    /**
     * Преобразование из строки в двоичный код
     * @return string
     */
    private function getEncodeString()
    {
        $result = '';

        for($i = 0; $i < strlen($this->realStr); $i++){
            $result .= $this->codeTable[$this->realStr[$i]];
        }

        return $result;
    }

    /**
     * Обёртка для кодирования строки
     * @param string $realStr
     * @return string
     */
    public function encode($realStr)
    {
        $this->makeList($realStr);
        $this->createHuffmansTree();
        $this->HuffmanRecursion($this->data);

        return $this->getEncodeString();
    }

    /**
     * Декодирование строки из '0' и '1' на основе $codeTable
     * Если $codeTable Не передана, используется полученная ранее таблица
     *
     * @param $codeStr
     * @param null $codeTable
     */
    public function decode($codeStr, $codeTable = null)
    {
        if($codeTable){
            $this->codeTable = $codeTable;
        }

        $decodeTableFlip = array_flip($this->codeTable);
        $result = [];

        $i = 0;
        while($i < strlen($codeStr)){
            $j = $i + 1;
            while(!array_key_exists(substr($codeStr, $i, $j - $i), $decodeTableFlip)){
                $j = $j + 1;
            }

            $result[] = $decodeTableFlip[substr($codeStr, $i, $j - $i)];
            $i = $j;
        }

        $str = '';
        foreach($result as $chr){
            $str .= $chr;
        }

        return $str;
    }

    /**
     * Возвращает таблицу кодирования
     * @return array|bool
     */
    public function getTableCode()
    {
        if($this->codeTable){
            return $this->codeTable;
        }

        return false;
    }
}

$huffman = new Huffman();
$codeStr = $huffman->encode('beep bop beer!');

echo $codeStr; # закодированая строка

print_r($huffman->getTableCode()); # табилца кодирования

echo $huffman->decode($codeStr); # раскодированная строка
