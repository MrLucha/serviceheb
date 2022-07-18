<?php
include ('simple_html_dom.php');
include ('conection.php');

require('Product.php');

//Iniciar conexion a la BD
$conexion= new Conection();
$mysqli=$conexion->connect();

//Se hace un arreglo para lista de objetos PRODUCTO
$lista_productos = array();

//Se hace la consulta a la BD de los productos con el ID de la tienda
$idTienda=1;
$query = $mysqli->query("SELECT * FROM list_items where id_list=".$idTienda);

//Se recorre cada uno de los resultados
foreach ($query as $q) {
    //Se crea un objeto PRODUCTO formado de la base de datos
    $producto = new Product();
    $producto->setIdList(intval($q['id_list']));
    $producto->setIdProduct(intval($q['id_product']));
    $producto->setPrice($q['price']);
    $producto->setLink($q['link']);
    //Se hace push al arreglo para agragar el producto
    array_push($lista_productos, $producto);
}

//Se hace un foreach para recorrer el array de PRODUCTOS
foreach ($lista_productos as $p) {
    //Se hace get link y get price
    $idList=$p->idList;
    $idProduct=$p->idProduct;
    $price=floatval($p->price);
    $link=$p->link;

    //Se hace el scrap con el link del producto
    $newPrice=floatval(scrapProducts($link));

    //Se evalua si el precio es diferente al de la BD
    if ($price!=$newPrice) {
        //Si es diferente, se ejecuta la funcion updatePrice
        updatePrice($newPrice,$idList,$idProduct);
    }else{
        // echo('<br>');
        // echo("no es diferente");
    }

    //Fin de un producto
}

//Se acaba el arreglo y acaba la tarea

//Cerrar conexion a la BD
$conexion->close();





function scrapProducts($link){
    //igualar url a link del objeto
    $url=$link;
    $html=file_get_html($url);

    //Busca si hay precio especial
    $buscador = $html->find('.special-price', 0);

    //Si hay precio especial
    if ($buscador!=null) {
        //echo("si hay");
        $nombre=$html->find('span[class=special-price]');
        foreach ($nombre as $elemento) {
            $precio=$elemento->find('span[class=price]');
            foreach ($precio as $p) {
                $p=$p->text();
                $eliminar = array("\t", "$", " ");
                $precio_producto = str_replace ( $eliminar, '', $p);
                return $precio_producto;
            }
        }
    }
    //Si no hay precio especial
    else {
        //echo("no hay");
        $precio=$html->find('span[class=price]');
        foreach ($precio as $p) {
            $p=$p->text();
            $eliminar = array("\t", "$", " ");
            $precio_producto = str_replace ( $eliminar, '', $p);
            return $precio_producto;
        }
    }

// return json_encode($GLOBALS["array_productos"],JSON_UNESCAPED_UNICODE);
}

function updatePrice($newPrice,$idList,$idProduct){
    //Se hace la consulta para insertar price en list items de acuerdo 
    //al id del proudcto y id de tienda (falta crear query)
    //query
    $newPrice=number_format((float)$newPrice, 2, '.', '');
    $query="UPDATE list_items SET price=$newPrice WHERE  id_list=$idList AND id_product=$idProduct";
    try {
        $GLOBALS['mysqli']->query($query);
        echo("se ha actualizado el producto correctamente");
        echo('<br>');
    } catch (\Throwable $th) {
        echo("error: $th");
    }
}

?>