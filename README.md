# **API - La comanda**

- Nombre del alumno: Alejo Martin Carmona.
- División: 3D.
- Año: 2023.

<hr>

**NOTA 1**: Esta aplicación viene con las tablas en SQL cargadas para poder probar su funcionamiento: 
- Productos: Viene con 14 productos.
- Usuarios: Viene con los 3 socios del restaurante y 5 empleados.
- Mesas: Viene con 5 mesas cargadas.
- Pedidos: Viene con 2 pedidos hechos en una mesa, ya finalizados.
- Encuesta: Viene con una encuesta realizada acerca del pedido mencionado.

**NOTA 2:** También es **importante saber** que se ha modificado la hora de PHP y la hora de MySQL para que ambas funcionen en UTC +0. De no tenerse esto en cuenta, la función de "Obtener tiempo restante para un pedido" no funcionaría correctamente.

## Objetivo

El objetivo de esta API desarrollada para un restaurante, es tomar los pedidos realizados y hacer de comanda, permitiendo que los empleados accedan a los mismos para poder cumplir con su labor según su puesto.

La API cuenta con 5 entidades:
- Mesa
- Producto
- Usuario
- Pedido
- Encuesta

## Descripción de las entidades

## **Usuario**

La entidad "Usuario" representa a cada uno de los empleados del restaurante. Los puestos disponibles para estos usuarios son:

- Cocinero
- Bartender
- Cervecero
- Mozo
- Socio

Su estructura completa es la siguiente:
- id
- nombre
- apellido
- dni
- puesto (cocinero/cervecero/bartender/mozo/socio)
- sector (cocina/barraChoperas/barraTragos/candyBar)
- fechaAlta
- email
- clave
- activo: 'true' por defecto

Las operaciones relacionadas con el manejo de usuarios están bajo ruteadas bajo la URL: http://localhost:3000/usuarios

### Operaciones

### Crear un usuario

Ruta: http://localhost:3000/usuarios</br>
Método HTTP: POST</br>
Puestos autorizados: Socios.

Para dar de alta un usuario, es necesario proporcionar los parámetros:
- nombre
- apellido
- dni
- email
- clave
- puesto (cocinero/cervecero/bartender/mozo/socio)
- sector (cocina/barraChoperas/barraTragos/candyBar)

Tanto el *dni* como el *email* no pueden existir en la base de datos al momento de hacer el alta.</br>
ATENCIÓN: Para dar de alta un mozo o socio, el parámetro *sector* no debe proporcionarse, puesto que ni los mozos ni los socios tienen un sector definido para trabajar.

### Modificación de usuario

Ruta: http://localhost:3000/usuarios</br>
Método HTTP: PUT</br>
Puestos autorizados: Socios.

Para modificar un usuario, es necesario proporcionar los parámetros:
- id
- nombre
- apellido
- dni
- email
- puesto (cocinero/cervecero/bartender/mozo/socio)

ATENCIÓN: Para cambiar el puesto del usuario a mozo o socio, el parámetro *sector* no debe proporcionarse, puesto que ni los mozos ni los socios tienen un sector definido para trabajar.

### Borrar un usuario

Ruta: http://localhost:3000/usuarios/{dni}</br>
Método HTTP: DELETE</br>
Puestos autorizados: Socios.

Para eliminar un usuario, es necesario proporcionar a través de la URL el DNI del usuario a eliminar. La baja realizada es una baja lógica, a través de un atributo propio de la entidad llamado "activo".

### Obtener todos los usuarios

Ruta: http://localhost:3000/usuarios</br>
Método HTTP: GET</br>
Puestos autorizados: Socios.

Para obtener el listado total de usuarios, simplemente se debe llamar a la URL proporcionada.

### Obtener todos los usuarios por puesto

Ruta: http://localhost:3000/usuarios/puesto/{puesto}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios.

Para obtener el listado total de usuarios, es necesario proporcionar a través de la URL el puesto de los usuarios buscados.

### Obtener todos los usuarios por puesto

Ruta: http://localhost:3000/usuarios/puesto/{puesto}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios.

Para obtener el listado total de usuarios, es necesario proporcionar a través de la URL el puesto de los usuarios buscados.

### Obtener usuario por DNI

Ruta: http://localhost:3000/usuarios/{dni}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios.

Para obtener un usuario, es necesario proporcionar a través de la URL el DNI del usuario buscado.

## **Mesa**

La entidad "Mesa" representa a cada una de las mesas del restaurante.
Tiene diferentes estados que indican el momento en el que se encuentra la misma, dentro del ciclo de vida del pedido:
- cerrada: significa que la mesa ha finalizado con su último pedido y está lista para tomar nuevos clientes.
- con cliente esperando pedido: el cliente ya ha realizado los pedidos correspondientes y aún no se le ha entregado ninguno.
- con cliente comiendo: se ha entregado por lo menos un plato de comida en la mesa y el cliente ya se encuentra  comiendo.
- con cliente pagando: el cliente ha dejado de comer y se le ha entregado al mismo la factura de todo lo consumido.

La mesa cuenta con un código de identificación alfanumérico de 5 dígitos que identifica a la misma de manera unívoca. Su estructura completa de una mesa es la siguiente:
- id
- estado: 'cerrada' por defecto
- asientos
- codigoIdentificacion: creado automáticamente por la aplicación de manera unívoca.
- activa: 'true' por defecto
- fechaCreacion

Las operaciones relacionadas con el manejo de mesas están bajo ruteadas bajo la URL: http://localhost:3000/mesas

### Operaciones

### Crear una mesa

Ruta: http://localhost:3000/mesas</br>
Método HTTP: POST</br>
Puestos autorizados: Socios.

Para dar de alta una mesa, es necesario proporcionar a través del body los parámetros:
- asientos: representa la cantidad de asientos disponibles en la mesa.

### Modificación de mesa

Ruta: http://localhost:3000/mesas</br>
Método HTTP: PUT</br>
Puestos autorizados: Socios.

Para modificar una mesa, es necesario proporcionar a través del body los parámetros:
- id
- asientos

### Borrar una mesa

Ruta: http://localhost:3000/mesas/{codigoIdentificacion}</br>
Método HTTP: DELETE</br>
Puestos autorizados: Socios.

Para borrar una mesa, es necesario proporcionar a través de la URL el código alfanumérico de 5 dígitos de la mesa a eliminar.

### Cambiar el estado de una mesa

Ruta: http://localhost:3000/mesas/cambioEstado</br>
Método HTTP: POST</br>
Puestos autorizados: Socios, mozos.

Para cambiar el estado de una mesa, es necesario proporcionar su código de alfanumérico de 5 digitos a través del body:
- codigoIdentificacion

Los cambios de estado se manejan de la siguiente manera:
- 'cerrada' -> 'con cliente esperando pedido': Es automático. Se realiza cuando se toma el primer pedido para la mesa.
- 'con cliente esperando pedido' -> 'con cliente comiendo': Es automático y realizado cuando algun cocinero, bartender o cervecero define que un pedido fue entregado.
- 'con cliente comiendo' -> 'con cliente pagando': Este cambio lo realiza el mozo una vez que el cliente ha terminado de comer y decide pagar la cuenta.
- 'con cliente pagando' -> 'cerrada': Este cambio lo realiza uno de los socios una vez que el cliente ha pagado la cuenta y procede a retirarse.

Si uno de los cambios no puede realizarse, se devolverá un mensaje de error.

### Obtener todas las mesas

Ruta: http://localhost:3000/mesas</br>
Método HTTP: GET</br>
Puestos autorizados: Socios, bartenders, cerveceros, cocineros, mozos.

Para obtener todas las mesas, simplemente se debe llamar a la URL proporcionada.

### Obtener una mesa

Ruta: http://localhost:3000/mesas/{codigoIdentificacion}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios, bartenders, cerveceros, cocineros, mozos.

Para obtener traer una mesa, es necesario proporcionar a través de la URL el código de la mesa buscada.

## Producto

La entidad 'Producto' representa a cada uno de los productos del restaurante. Su estructura completa es la siguiente:
- id
- nombre
- tipo: comida o bebida
- sector: cocina, candyBar, barraTragos o barraChoperas.
- precio
- activo: 'true' por defecto
- fechaIncorporacion

Las operaciones relacionadas con el manejo de los productos están bajo ruteadas bajo la URL: http://localhost:3000/productos

### Operaciones

### Crear un producto

Ruta: http://localhost:3000/productos</br>
Método HTTP: POST</br>
Puestos autorizados: Socios.

Para dar de alta un producto, es necesario proporcionar a través del body los siguientes parámetros:
- nombre
- tipo: comida o bebida.
- sector: cocina, candyBar, barraTragos o barraChoperas.
- precio

### Modificación de un producto

Ruta: http://localhost:3000/productos</br>
Método HTTP: PUT</br>
Puestos autorizados: Socios.

Para modificar un producto, es necesario proporcionar a través del body los siguientes parámetros:
- id
- tipo: comida o bebida.
- sector: cocina, candyBar, barraTragos o barraChoperas.
- precio

### Borrar un producto

Ruta: http://localhost:3000/productos/{id}</br>
Método HTTP: DELETE</br>
Puestos autorizados: Socios.

Para borrar un producto, es necesario proporcionar a través de la URL el id del producto a eliminar.

### Obtener un producto

Ruta: http://localhost:3000/productos/{id}</br>
Método HTTP: GET</br>
Puestos autorizados: No hay restricción.

Para obtener un producto, es necesario proporcionar a través de la URL el id del producto buscado.

### Obtener todos los productos

Ruta: http://localhost:3000/productos</br>
Método HTTP: GET</br>
Puestos autorizados: No hay restricción.

Para obtener todos los productos, simplemente se debe llamar a la URL proporcionada.

### Descarga de productos mediante CSV

Si por algún motivo se desea descargar todos los productos, la plataforma proporciona una herramienta de descarga que devolverá un archivo CSV con el listado de todos los existentes, activos o no.

Ruta: http://localhost:3000/productos/csv</br>
Método HTTP: GET</br>
Puestos autorizados: No hay restricción.

Al llamar a esta URL, se le descargá al usuario un CSV con el listado de los productos, tal cual como se encuentran en la base de datos.

### Carga de productos mediante CSV

El socio también posee la opción de cargar todos los productos a través de la carga de un archivo CSV que debe tener SÍ O SÍ la misma estructura que el archivo descargado.

Ruta: http://localhost:3000/productos/csv</br>
Método HTTP: POST</br>
Puestos autorizados: Socios.

Para cargar los productos, se le debe proporcionar una archivo CSV a través del body. Este parámetro debe llamarse 'listaProductos'.

## Pedido

La entidad 'Pedido' representa a los pedidos realizados en el restaurante. En este caso, un pedido completo representa a muchos pedidos realizados en una misma mesa en otras palabras, una mesa puede tener muchos pedidos sin embargo, cada vez que se agrega un producto porque una mesa lo solicitó, se crea como un nuevo pedido, y se relaciona con el resto a través de un código alfanumérico de 5 dígitos creado de manera única. 
La estructura completa de un pedido es:
- id
- codigoMesa: código alfanumérico de 5 dígitos que identifica a una mesa.
- idProducto: id del producto pedido.
- nombreCliente
- codigoIdentificacion: código alfanumérico de 5 dígitos que identifica a un pedido.
- estado: 'pendiente' por defecto.
- tiempoPreparacion: NULL por defecto. Debe ser proporcionado posteriormente por un empleado al momento de tomarlo para su preparación.
- fecha

Las operaciones relacionadas con el manejo de pedidos están bajo ruteadas bajo la URL: http://localhost:3000/pedidos

### Operaciones

### Crear un pedido

Ruta: http://localhost:3000/pedidos</br>
Método HTTP: POST</br>
Puestos autorizados: Mozos.

Para dar de alta un pedido, es necesario proporcionar a través del body los siguientes parámetros:
- codigoMesa: codigo alfanumérico de 5 dígitos.
- idProducto: id del producto pedido.
- nombreCliente

Si el código de la mesa proporcionado está activo y el producto solicitado también está activo, se procede a generar un nuevo pedido. Además, en el caso de que el código de la mesa represente a una mesa que no esté en uso (su estado debe ser 'cerrada'), se genera un código alfanumérico de 5 dígitos y se cambiará el estado de la mesa a 'con cliente esperando comida'. Este código identificará el pedido recién creado y los futuros pedidos asociados a esa mesa específica. En el caso de que la mesa no esté cerrada, se utilizará el código alfanumérico previamente generado para los pedidos de esa mesa. El estado del pedido recién creado será siempre 'pendiente', para que los empleados del restaurante puedan tomar los pedidos según el sector en el que se encuentren trabajando, asignándole un tiempo estimado de preparación a cada uno.

### Modificar un pedido

Ruta: http://localhost:3000/pedidos</br>
Método HTTP: PUT</br>
Puestos autorizados: Mozos.

Para modificar un pedido, es necesario proporcionar a través del body los siguientes parámetros:
- id
- idProducto
- nombreCliente

El ID del producto debe existir para poder modificar el producto.

### Eliminar un pedido

La eliminación de un pedido consistirá en cancelar al mismo, por lo que su estado pasará a ser 'cancelado'.

Ruta: http://localhost:3000/pedidos/{id}</br>
Método HTTP: DELETE</br>
Puestos autorizados: Mozos.

Para eliminar un pedido, es necesario proporcionar a través de la URL el id del pedido a eliminar. Se hará una baja lógica.

### Obtener un pedido

Ruta: http://localhost:3000/pedidos/pedido/{id}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios, cocineros, cerveceros, bartenders, mozos.

Para obtener un pedido, es necesario proporcionar a través de la URL el id del pedido buscado.

### Obtener todos los pedidos

Ruta: http://localhost:3000/pedidos</br>
Método HTTP: GET</br>
Puestos autorizados: Socios.

Para obtener todos pedidos (que se hayan finalizado y no, activos o no), simplemente se debe llamar a la URL proporcionada.

### Obtener todos los pedidos de una mesa

Ruta: http://localhost:3000/pedidos/{codigoIdentificacion}</br>
Método HTTP: GET</br>
Puestos autorizados: No hay restricción.

Para obtener todos pedidos de una mesa, es necesario proporcionar a través de la URL el código de identificación alfanumérico de 5 dígitos del pedido buscado.

### Obtener tiempo restante para un pedido

La aplicación le permite al usuario obtener el tiempo restante para que su pedido completo esté listo. Para poder determinar este tiempo, no debe haber ningún pedido pendiente, puesto que debe  conocerse el tiempo restante de todos para utilizar el más alto y realizar el cálculo. Se le indica al usuario los minutos restantes para que su pedido esté completo o los minutos retrasados si es que el pedido está tardando más de lo esperado.

Ruta: http://localhost:3000/pedidos/tiempoRestante/{codigoMesa}/{codigoIdentificacion}</br>
Método HTTP: GET</br>
Puestos autorizados: No hay restricción.

Para obtener el tiempo restante de un pedido, es necesario proporcionar a través de la URL el código de identificación alfanumérico de 5 dígitos de la mesa, y también el del pedido buscado.

### Obtener pedidos pendientes por sector

Los empleados pueden conocer en todo momento los pedidos pendientes que tienen en el sector en el que están trabajando para poder trabajar en ellos.

Ruta: http://localhost:3000/pedidos/sector/{sector}</br>
Método HTTP: GET</br>
Puestos autorizados: Socios, cocineros, cerveceros, bartenders, mozos

Para obtener todos pedidos de un sector en particular, es necesario proporcionarle el nombre del mismo a través de la URL.

### Cambiar estado de un pedido

Los cocineros, bartenders y cerveceros tienen la habilidad de cambiar el estado de los pedidos. Estos pueden ser:
- pendiente: el estado ha sido enviado al sector para ser preparado pero aún no se ha comenzado con el proceso.
- en preparación: el pedido ha comenzado a ser preparado por un empleado, este le ha asignado un tiempo aproximado de preparacíon que es el que se utilizará si un usuario desea conocer el tiempo restante de preparación del pedido. 
- listo para servir: la preparación del producto ha finalizado y puede ser entregado
- entregado: el producto ha sido entregado a la mesa. Esta acción es realizada por los empleados que trabajan preparando los productos de los pedidos para que el mozo no tenga la responsabilidad de marcar a los mismos como "entregado". De esta manera se evita malentendidos entre estos empleados y los mozos, entendiendo que un plato es "entregado" una vez que los cocineros/cerveceros/bartenders observan que el pedido ha salido de su puesto.

Ruta: http://localhost:3000/pedidos/cambioEstado</br>
Método HTTP: POST</br>
Puestos autorizados: Cocineros, cerveceros, bartenders.

Para poder cambiar el estado de un pedido, se deben proporcionar los siguientes parámetros a través del body:
- id
- tiempoPreparacion: el tiempo que tarda el pedido en prepararse expresado en segundos.

### Subir foto de una mesa

El mozo tiene la posibilidad de subir una foto de una mesa con sus integrantes para relacionarla a los pedidos de una misma mesa una vez que este fue creado.

Ruta: http://localhost:3000/pedidos/foto</br>
Método HTTP: POST</br>
Puestos autorizados: Mozos.

Para poder subir la foto de una mesa, se deben proporcionar los siguientes parámetros a través del body:
- foto: foto de la mesa.
- codigoIdentificacion: codigo alfanumérico de 5 dígitos que identifica a los pedidos de una mesa.

## Login

Como tal, Login no es una entidad, pero sí un recurso necesario para los empleados del restaurante. Este endpoint les permite loguearse en la aplicación para comenzar a utilizar ciertos recursos de la misma.

### Operaciones

### Iniciar sesión

Ruta: http://localhost:3000/login</br>
Método HTTP: POST</br>
Puestos autorizados: No hay restricción.

Para poder iniciar sesión, se deben proporcionar los siguientes parámetros a través del body:
- email
- clave

Se le notificará al usuario si el email no existe, si la contraseña es incorrecta, o se le devolverá el token JWT generado si se udo realizar el inicio de sesión correctamente.

## Encuesta

Esta entidad sirve para representar a las encuentas realizadas por los clientes sobre su atención en el restaurante. La misma se habilita una vez que el cliente terminó de pagar, esto quiere decir que la mesa asociada a la misma debe estar con el estado 'con cliente pagando'.
NOTA: Esta entidad no era necesaria al momento del tercer sprint, pero decidí agregarla con su mínima funcionalidad (crear una encuesta) con el objetivo de finalizar con el ciclo completo de un pedido. Su estructura completa es la siguiente:
- id
- codigoPedido
- puntuacionMesa
- puntuacionRestaurante
- puntuacionMozo
- puntuacionCocinero
- descripcionExperiencia
- fecha

### Operaciones

### Crear nueva encuesta

El usuario tiene la posibilidad de evaluar la atención recibida en varios puntos. La creación de la encuesta es posible una vez que el cliente terminó de comer. Se pueden realizar muchas reseñas para un mismo pedido, puesto que los comensales pueden ser varios.

Ruta: http://localhost:3000/encuestas</br>
Método HTTP: POST</br>
Puestos autorizados: No hay restricción.

Para poder crear la encuesta, se deben proporcionar los siguientes parámetros a través del body:
- codigoPedido: codigo alfanumérico de 5 dígitos que identifica a todos los pedidos realizados en esa mesa.
- puntuacionMesa
- puntuacionRestaurante
- puntuacionMozo
- puntuacionCocinero
- descripcionExperiencia: una breve descripción de unos 66 caractéres como máximo acerca de la atención recibida.

## Flujo de los pedidos

1. Se da de alta un primer pedido con un producto, esto hará que la mesa asociada al mismo cambio su estado de 'cerrada' a 'con cliente esperando pedido', también generará un código alfanumérico de 5 dígitos que permitirá asociar los siguientes pedidos de la mesa con un mismo código. 
2. Los empleados toman los pedidos y los preparan, cambiando el estado de los mismos en cada paso. El usuario puede, si es que todos los pedidos ya no están pendientes, determinar el tiempo restante de entrega de los mismos.
3. A medida que los pedidos se finalizan, se entregan a la mesa. Esto hace que el estado de la misma pase de 'con cliente esperando pedido' a 'con cliente comiendo'.
4. Una vez que el cliente termina de comer y procede a pedir la cuenta, se cambia el estado de la mesa de 'con cliente comiendo' a 'con cliente pagando', indicándole al mismo el monto final a abonar. El cliente puede, en este momento, dejar una reseña acerca de su atención recibida (se pueden realizar muchas reseñas para un mismo pedido, puesto que los comensales pueden ser varios).
5. Una vez que el cliente pagó la cuenta, el socio cambia el estado de la mesa de 'con cliente pagando' a 'cerrada', dejando a la mesa preparada para recibir nuevos clientes.

<hr>