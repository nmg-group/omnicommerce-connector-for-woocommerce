# Omnicommerce Connector for WooCommerce

Qué estoy metiendo en mi web? va a hacer más lento mi sitio? por qué necesito este plugin?

Todas preguntas válidas, lee más para saber las respuestas.


Este plugin tiene el sólo propósito de facilitar el acceso a los datos básicos que Omnicommerce necesita para mantener tu catálogo de productos actualizado.

**Qué hace exactamente?**

Este código añade un endpoint más (una dirección de API más) a las ya existentes en WooCommerce que nos permite acceder a la lista de todos los productos y variantes de una manera mucho más rápida y con menos solicitudes a tu servidor. Ver ejemplos más abajo.

**Es un requisito instalar este plugin?**

No. No es un requisito aunque es altamente recomendable para reducir el impacto que nuestra plataforma de integraciones tendrá en el rendimiento de tu sitio.

**Va a poner más lenta mi web?**

No. Este plugin sólo ejecuta *trabajo* (con solicitudes a la base de datos), cuando recibe un llamado desde nuestra plataforma, por lo que su existencia entre tus plugins no enlentece tu web. Por el contrario, reduce la cantidad de veces que tenemos que contactarnos con tu servidor para sincronizar tu catálogo.



## Ejemplos de rendimiento

En este ejemplo obtenemos la lista de artículos completa para un catálogo con 500 artículos, cada uno con 5 variantes de talle y/o color.

**Sin este plugin:**

5 solicitudes para obtener 5 páginas de 100 artículos + 500 solicitudes para obtener las 5 variantes de cada artículo.

505 solicitudes a tu servidor / ~ 8 minutos.


**Con este plugin activo:**

3 solicitudes para obtener 3 páginas de 200 artículos + 3 solicitudes para obtener 3 páginas de 200 variantes

6 solicitudes a tu servidor / ~ 30 segundos.


**Finalmente**

Todo el código del plugin es público y se encuentra en un único archivo con menos de 100 líneas que puede revisar tu desarrollador web claro.
