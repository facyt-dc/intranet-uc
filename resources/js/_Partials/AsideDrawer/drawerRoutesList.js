// ************************************************************************************
// definicion de las rutas que se renderizaran en el drawer
// ************************************************************************************

/**
 * Lsita de objetos que representan a las utas que se mostraran en el menu de navegacion
 * @namespaces
 * @property {Object} RouteList objeto que representa al titulo de una seccion de rutas y dichas rutas
 *
 * @property {Boolean} RouteList.permissionNeeded indica si el usuarios tiene el permiso para acceder a dicha ruta
 * @property {String} RouteList.subHeaderText indica el ambito de las rutas que abarca
 * @property {Array} RouteList.routes array de rutas pertenecientes a una seccion
 *
 * @property {Object} route objeto que indica una ruta
 * @property {String} route.linkText texto que se mostrara alusuario en el link
 * @property {String} route.routeName nombre de la ruta ala cual se redirecciona
 *
 */
const drawerRoutesList = [
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "Navegacion",
        routes: [
            {
                linkText: "Dashboard",
                routeName: "dashboard",
            },
            {
                linkText: "Hola",
                routeName: "hola",
            },
        ],
    },
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "roles y permisos",
        routes: [
            {
                linkText: "Roles",
                routeName: "admin.role.index",
            },
            {
                linkText: "Permisos",
                routeName: "admin.permission.index",
            },
        ],
    },
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "Usuarios",
        routes: [
            {
                linkText: "Usuarios",
                routeName: "admin.user.index",
            },
        ],
    },
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "Documentos",
        routes: [
            {
                linkText: "Oficios",
                routeName: "document.index",
            },
        ],
    },
    {
        // El menú solo aparecerá si el usuario tiene este permiso.
        permissionNeeded: "inventory.access",

        // Título de la sección en el menú lateral.
        subHeaderText: "Inventario",

        // Lista de enlaces para el módulo.
        routes: [
            {
                linkText: "Ítems",
                routeName: "admin.item.index",
            },
            {
                linkText: "Categorías",
                routeName: "admin.item-category.index",
            },
            {
                linkText: "Ubicaciones",
                routeName: "admin.item-location.index",
            },
            {
                linkText: "Estados",
                routeName: "admin.item-status.index",
            },
            {
                linkText: "Movimientos",
                routeName: "admin.item-inventory-movement.index",
            },
            {
                linkText: "Tipos de Movimiento",
                routeName: "admin.item-movement-type.index",
            },
        ],
    },
];

export default drawerRoutesList;

// ************************************************************************************
// definicion de las rutas que se renderizaran en el drawer
// ************************************************************************************
