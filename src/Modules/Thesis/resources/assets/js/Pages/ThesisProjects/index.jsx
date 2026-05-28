import React, { useState, useMemo } from "react"; // <--- CAMBIO: Añadido useState y useMemo
import { Head, Link } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import Alert from "@/Components/Alert";
import Table from "./components/Table";
import { useTranslation } from "react-i18next";

// --- CAMBIO: Añadimos todos los imports de Material-UI necesarios ---
import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import Paper from "@mui/material/Paper";
import Grid from "@mui/material/Grid";
import TextField from "@mui/material/TextField";
import Select from "@mui/material/Select";
import MenuItem from "@mui/material/MenuItem";
import FormControl from "@mui/material/FormControl";
import InputLabel from "@mui/material/InputLabel";
import TablePagination from "@mui/material/TablePagination";

// El componente recibe 'thesis' como antes, con la lista completa de proyectos.
export default function ThesisProjectIndex({ auth, thesis, flash }) {
    const alert = flash?.alert;
    const { t } = useTranslation("common");

    // --- CAMBIO: Añadimos el estado para los filtros y la paginación ---
    const [filters, setFilters] = useState({ title: '', student: '', status: '' });
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    
    // --- CAMBIO: Añadimos la lógica para filtrar los datos ---
    const filteredThesis = useMemo(() => {
        // Si no hay datos, devuelve un array vacío para evitar errores
        if (!thesis) return [];
        
        return thesis.filter(thesisItem => {
            const titleFilter = filters.title.toLowerCase();
            const studentFilter = filters.student.toLowerCase();
            const statusFilter = filters.status;

            // Condición para el filtro de título
            const titleMatch = !titleFilter || (thesisItem.title && thesisItem.title.toLowerCase().includes(titleFilter));

            // Condición para el filtro de estudiante (ahora es segura)
            // Comprueba si el array 'students' existe antes de intentar buscar en él
            const studentMatch = !studentFilter || 
                                 (Array.isArray(thesisItem.students) && 
                                  thesisItem.students.some(s => s.name && s.name.toLowerCase().includes(studentFilter)));

            // Condición para el filtro de estado (activo/inactivo)
            const statusMatch = statusFilter === '' || thesisItem.is_active == statusFilter;

            return titleMatch && studentMatch && statusMatch;
        });
    }, [thesis, filters]);

    // --- CAMBIO: Añadimos la lógica para paginar los datos ya filtrados ---
    const paginatedThesis = useMemo(() => {
        return filteredThesis.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage);
    }, [filteredThesis, page, rowsPerPage]);

    // --- CAMBIO: Añadimos las funciones para manejar los cambios en los filtros y la paginación ---
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => ({ ...prev, [name]: value }));
        setPage(0); // Volver a la primera página cuando se aplica un filtro
    };
    const handleChangePage = (event, newPage) => setPage(newPage);
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(parseInt(event.target.value, 10));
        setPage(0);
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={"Proyectos de Tesis"} />
            {alert && <Alert key={alert.id} message={alert.message} severity={alert.severity} />}
            
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-xl text-gray-500 capitalize">{t("Listado De Proyectos De Tesis")}</h2>
                <div className="flex items-center space-x-2">
                    <Link href={route("Thesis.create")}>
                        <Button variant="contained" startIcon={<AddRoundedIcon />}>
                            {"Agregar proyecto de tesis"}
                        </Button>
                    </Link>
                </div>
            </div>

            {/* --- CAMBIO: Añadimos la sección de filtros --- */}
            <Paper elevation={3} sx={{ p: 2, mb: 2 }}>
                <Grid container spacing={2} alignItems="center">
                    <Grid item xs={12} md={4}>
                        <TextField
                            fullWidth
                            label="Buscar por Título"
                            variant="outlined"
                            size="small"
                            name="title"
                            value={filters.title}
                            onChange={handleFilterChange}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <TextField
                            fullWidth
                            label="Buscar por Estudiante"
                            variant="outlined"
                            size="small"
                            name="student"
                            value={filters.student}
                            onChange={handleFilterChange}
                        />
                    </Grid>
                    <Grid item xs={12} md={4}>
                        <FormControl fullWidth size="small">
                            <InputLabel>Estado</InputLabel>
                            <Select label="Estado" name="status" value={filters.status} onChange={handleFilterChange}>
                                <MenuItem value=""><em>Todos</em></MenuItem>
                                <MenuItem value="1">Activo</MenuItem>
                                <MenuItem value="0">Inactivo</MenuItem>
                            </Select>
                        </FormControl>
                    </Grid>
                </Grid>
            </Paper>

            {/* CAMBIO: Pasamos a la tabla solo los datos de la página actual */}
            <Table thesis={paginatedThesis} />

            {/* --- CAMBIO: Añadimos la sección de paginación --- */}
            <Paper elevation={3} sx={{ mt: 2 }}>
                <TablePagination
                    component="div"
                    count={filteredThesis.length}
                    page={page}
                    onPageChange={handleChangePage}
                    rowsPerPage={rowsPerPage}
                    onRowsPerPageChange={handleChangeRowsPerPage}
                    rowsPerPageOptions={[5, 10, 25, 50]}
                    labelRowsPerPage="Filas por página:"
                    labelDisplayedRows={({ from, to, count }) => `${from}–${to} de ${count !== -1 ? count : `más de ${to}`}`}
                />
            </Paper>
        </AdminLayout>
    );
}