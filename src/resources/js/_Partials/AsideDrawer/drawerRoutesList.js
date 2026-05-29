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
        permissionNeeded: null,
        subHeaderText: "Navegacion",
        routes: [
            {
                linkText: "Dashboard",
                routeName: "dashboard",
            }
        ]
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
        permissionNeeded: "view agendas",
        subHeaderText: "Agenda de consejos",
        routes: [
            {
                linkText: "Agendas",
                routeName: "agendas.index",
            }
        ],
    },
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "Tesis",
        routes: [
            {
                linkText: "Tesistas",
                routeName: "thesisStudent.index",
            },
            {
                linkText: "Docentes",
                routeName: "thesisTeacher.index"
            },
            {
                linkText: "Estatus de estudiantes",
                routeName: "studentStatuses.index",
            },
            {
                linkText: "Proyectos PTEG/TEG",
                routeName: "Thesis.index",
            },
            {
                linkText: "Diagrama General de Tesistas",
                routeName: "thesis.ganttChart",
            },
        ],
    },
    {
        permissionNeeded: "isAdmin",
        subHeaderText: "Empleados",
        routes: [
            {
                linkText: "Cargos",
                routeName: "employee.staff.index",
            },
            {
                linkText: "Empleados",
                routeName: "employee.index",
            },
            {
                linkText: "Beneficios",
                routeName: "employee.benefit.index",
            },
            {
                linkText: "Tipos de Personal",
                routeName: "employee.staff.type.index",
            },
            {
                linkText: "Niveles de Docencia",
                routeName: "employee.teaching.level.index",
            },
            {
                linkText: "Historial de Beneficios",
                routeName: "employee.benefit.history.index",
            },
        ],
    },
    {
        permissionNeeded: "isTechnician",
        subHeaderText: "Mantenimiento",
        routes: [
            {
                linkText: "Solicitudes de mantenimiento",
                routeName: "mantenimiento.index",
            },
            {
                linkText: "Gestionar Etapas",
                routeName: "mantenimiento.stages.index",
            },
            {
                linkText: "Gestionar Equipos",
                routeName: "mantenimiento.equipment.index",
            },
            {
                linkText: "Gestionar Categorías",
                routeName: "mantenimiento.equipment.categories.index",
            },
        ],
    },
    {
        permissionNeeded: "isDirector",
        subHeaderText: "Opciones del Director",
        routes: [
            {
                linkText: "Opciones de votacion",
                routeName: "settings.voting-options.index",
            }
        ],
    },
];

export default drawerRoutesList;

// ************************************************************************************
// definicion de las rutas que se renderizaran en el drawer
// ************************************************************************************
