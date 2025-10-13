import React, { useState, useMemo } from "react"; // <--- CAMBIO: Añadido useState y useMemo
import { Head, Link } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import Alert from "@/Components/Alert";
import Table from "@/Pages/Thesis/ThesisStudent/components/Table";
import File from "./components/File";
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

// El componente recibe 'thesisStudent' como antes.
export default function ThesisStudentIndex({ auth, thesisStudent, flash }) {
    const alert = flash?.alert;
    const { t } = useTranslation("common");

    // --- CAMBIO: Añadimos el estado para los filtros y la paginación ---
    const [filters, setFilters] = useState({ name: '', status_id: '' });
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);

    // --- CAMBIO: Creamos la lista de estados únicos para el dropdown de filtros ---
    const statuses = useMemo(() => {
        if (!thesisStudent) return [];
        // Usamos un Map para obtener solo los estados únicos basados en su ID
        const uniqueStatuses = [...new Map(thesisStudent.map(item => [item.status?.id, item.status])).values()];
        // Filtramos cualquier posible valor nulo o indefinido y ordenamos alfabéticamente
        return uniqueStatuses.filter(Boolean).sort((a, b) => a.name.localeCompare(b.name));
    }, [thesisStudent]);

    // --- CAMBIO: Añadimos la lógica para filtrar los datos ---
    const filteredStudents = useMemo(() => {
        if (!thesisStudent) return [];
        return thesisStudent.filter(student => {
            const nameFilter = filters.name.toLowerCase();
            const statusFilter = filters.status_id;
            const nameMatch = !nameFilter || student.name.toLowerCase().includes(nameFilter) || student.email.toLowerCase().includes(nameFilter);
            const statusMatch = !statusFilter || student.status_id == statusFilter;
            return nameMatch && statusMatch;
        });
    }, [thesisStudent, filters]);

    // --- CAMBIO: Añadimos la lógica para paginar los datos ya filtrados ---
    const paginatedStudents = useMemo(() => {
        return filteredStudents.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage);
    }, [filteredStudents, page, rowsPerPage]);

    // --- CAMBIO: Añadimos las funciones para manejar los cambios en los filtros y la paginación ---
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => ({ ...prev, [name]: value }));
        setPage(0);
    };
    const handleChangePage = (event, newPage) => setPage(newPage);
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(parseInt(event.target.value, 10));
        setPage(0);
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={"Tesistas"} />
            {alert && <Alert key={alert.id} message={alert.message} severity={alert.severity} />}
            
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-xl text-gray-500 capitalize">{t("Listado De Tesistas")}</h2>
                <div className="flex items-center space-x-2">
                    <File />
                    <Link href={route("thesisStudent.create")}>
                        <Button variant="contained" startIcon={<AddRoundedIcon />}>
                            {"Agregar tesista"}
                        </Button>
                    </Link>
                </div>
            </div>

            {/* --- CAMBIO: Añadimos la sección de filtros --- */}
            <Paper elevation={3} sx={{ p: 2, mb: 2 }}>
                <Grid container spacing={2} alignItems="center">
                    <Grid item xs={12} md={6}>
                        <TextField
                            fullWidth
                            label="Buscar por Nombre o Email"
                            variant="outlined"
                            size="small"
                            name="name"
                            value={filters.name}
                            onChange={handleFilterChange}
                        />
                    </Grid>
                    <Grid item xs={12} md={6}>
                        <FormControl fullWidth size="small">
                            <InputLabel>Estado</InputLabel>
                            <Select label="Estado" name="status_id" value={filters.status_id} onChange={handleFilterChange}>
                                <MenuItem value=""><em>Todos</em></MenuItem>
                                {statuses.map((status) => (
                                    <MenuItem key={status.id} value={status.id}>{status.name}</MenuItem>
                                ))}
                            </Select>
                        </FormControl>
                    </Grid>
                </Grid>
            </Paper>

            {/* CAMBIO: Pasamos a la tabla solo los datos de la página actual */}
            <Table thesisStudent={paginatedStudents} />

            {/* --- CAMBIO: Añadimos la sección de paginación --- */}
            <Paper elevation={3} sx={{ mt: 2 }}>
                <TablePagination
                    component="div"
                    count={filteredStudents.length}
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