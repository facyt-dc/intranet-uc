import React, { useState, useMemo } from "react";
import { Head, Link } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import Alert from "@/Components/Alert";
import Table from "./components/Table";
import { useTranslation } from "react-i18next";

import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import Paper from "@mui/material/Paper";
import Grid from "@mui/material/Grid";
import TextField from "@mui/material/TextField";
import TablePagination from "@mui/material/TablePagination";

export default function ThesisTeacherIndex({ auth, teachers, flash }) {
    const alert = flash?.alert;
    const { t } = useTranslation("common");

    const [filters, setFilters] = useState({ name: '' });
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(10);

    const filteredTeachers = useMemo(() => {
        if (!teachers) return [];
        return teachers.filter(teacher => {
            const nameFilter = filters.name.toLowerCase();
            return !nameFilter || teacher.name.toLowerCase().includes(nameFilter) || teacher.email.toLowerCase().includes(nameFilter);
        });
    }, [teachers, filters]);

    const paginatedTeachers = useMemo(() => {
        return filteredTeachers.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage);
    }, [filteredTeachers, page, rowsPerPage]);

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
            <Head title={"Docentes"} />
            {alert && <Alert key={alert.id} message={alert.message} severity={alert.severity} />}
            
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-xl text-gray-500 capitalize">{t("Listado De Docentes")}</h2>
                <div className="flex items-center space-x-2">
                    {/* El botón de importar archivo se omite como solicitaste */}
                    <Link href={route("thesisTeacher.create")}>
                        <Button variant="contained" startIcon={<AddRoundedIcon />}>
                            {"Agregar Docente"}
                        </Button>
                    </Link>
                </div>
            </div>

            <Paper elevation={3} sx={{ p: 2, mb: 2 }}>
                <Grid container spacing={2} alignItems="center">
                    <Grid item xs={12}>
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
                </Grid>
            </Paper>

            <Table teachers={paginatedTeachers} />

            <Paper elevation={3} sx={{ mt: 2 }}>
                <TablePagination
                    component="div"
                    count={filteredTeachers.length}
                    page={page}
                    onPageChange={handleChangePage}
                    rowsPerPage={rowsPerPage}
                    onRowsPerPageChange={handleChangeRowsPerPage}
                    rowsPerPageOptions={[5, 10, 25, 50]}
                    labelRowsPerPage="Filas por página:"
                    labelDisplayedRows={({ from, to, count }) => `${from}–${to} de ${count}`}
                />
            </Paper>
        </AdminLayout>
    );
}