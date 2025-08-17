import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";

import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import Typography from "@mui/material/Typography";
import Grid from "@mui/material/Grid";
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { green } from '@mui/material/colors';
import Chip from '@mui/material/Chip';

export default function ThesisStudentShow({ auth, thesisStudent }) {
    const { t } = useTranslation(["translation", "common"]);

    const getStatusChipColor = (statusName) => {
        if (!statusName) return 'default';
        const name = statusName.toLowerCase();
        
        switch (name) {
            case 'inscrito':
            case 'activo':
            case 'en proceso':
                return 'success';
            case 'pteg inscrito':
            case 'culminado':
                return 'primary';
            case 'retirado':
            case 'suspendido':
                return 'error';
            case 'en espera':
                return 'warning';
            default:
                return 'default';
        }
    };

    const sortedTheses = useMemo(() => {
        // Si no hay tesis, devuelve un array vacío para evitar errores.
        if (!thesisStudent.theses || thesisStudent.theses.length === 0) {
            return [];
        }

        // Creamos una copia del array para no mutar el prop original.
        return [...thesisStudent.theses].sort((a, b) => {
            // Criterio 1: Ordenar por is_active (true va primero).
            // La resta de booleanos (true=1, false=0) lo hace de forma descendente.
            if (a.is_active !== b.is_active) {
                return b.is_active - a.is_active;
            }

            // Criterio 2: Si is_active es el mismo, ordenar por fecha (más nueva primero).
            return new Date(b.date) - new Date(a.date);
        });
    }, [thesisStudent.theses]);

    return (
        <AdminLayout auth={auth}>
            <Head title={t("Detalle Tesista", "Detalle Tesista")} />
            
            {/* SECCIÓN DE CABECERA MODIFICADA */}
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">
                    {t("Detalle del Tesista", "Detalle del Tesista")}
                </h2>
                
                <div className="flex items-center gap-4">
                    {/* Chip del estatus */}
                    {thesisStudent.status && (
                        <Chip 
                            label={thesisStudent.status.name} 
                            color={getStatusChipColor(thesisStudent.status.name)}
                        />
                    )}
                    
                    {/* Botones de acción */}
                    <Link href={route("thesisStudent.edit", thesisStudent.id)}>
                        <Button variant="contained" startIcon={<EditIcon />}>
                            {t("Editar", "Editar")}
                        </Button>
                    </Link>
                    <Link href={route("thesisStudent.index")}>
                        <Button variant="outlined">
                            {t("Volver", "Volver")}
                        </Button>
                    </Link>
                </div>
            </div>

            {/* SECCIÓN DE GRID CON DETALLES DEL ESTUDIANTE */}
            <Grid container spacing={3}>
                <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("Nombre", "Nombre")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">
                        {thesisStudent.name}
                    </Typography>
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("Email", "Email")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">
                        {thesisStudent.email}
                    </Typography>
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("ID UC", "ID UC")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">
                        {thesisStudent.id_uc}
                    </Typography>
                </Grid>
                <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("Cédula", "Cédula")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">
                        {thesisStudent.ci}
                    </Typography>
                </Grid>
            </Grid>

            {/* --- INICIO DE LA SECCIÓN DE TABLA DE TESIS (RESTAURADA) --- */}
            <div className="mt-8">
                <Typography variant="h6" gutterBottom>
                    {t("Proyectos de Tesis Asociados", "Proyectos de Tesis Asociados")}
                </Typography>
                
                {sortedTheses.length > 0 ? (
                    <TableContainer component={Paper}>
                        <Table sx={{ minWidth: 650 }} aria-label="simple table">
                            <TableHead>
                                <TableRow>
                                    <TableCell sx={{ fontWeight: 'bold' }}>{t("Título de la Tesis", "Título de la Tesis")}</TableCell>
                                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>{t("Fecha de Registro", "Fecha de Registro")}</TableCell>
                                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>{t("Estatus", "Estatus")}</TableCell>

                                    <TableCell align="center" sx={{ fontWeight: 'bold' }}>{t("Acciones", "Acciones")}</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {/* --- CAMBIO CLAVE: Usamos el nuevo array ordenado 'sortedTheses' --- */}
                                {sortedTheses.map((thesis) => {
                                    // La lógica para resaltar la fila activa sigue siendo la misma
                                    const isCurrentlyActive = thesis.is_active;

                                    return (
                                        <TableRow
                                            key={thesis.id}
                                            sx={{ 
                                                '&:last-child td, &:last-child th': { border: 0 },
                                                backgroundColor: isCurrentlyActive ? green[100] : 'inherit'
                                            }}
                                        >
                                            <TableCell component="th" scope="row">
                                                {thesis.title}
                                            </TableCell>
                                            
                                            <TableCell align="right">
                                                {new Date(thesis.date).toLocaleDateString()}
                                            </TableCell>
                                            
                                            <TableCell align="right">
                                                <Chip 
                                                    label={isCurrentlyActive ? 'Activo' : 'Inactivo'}
                                                    color={isCurrentlyActive ? 'success' : 'default'}
                                                    size="small"
                                                />
                                            </TableCell>
                                            
                                            <TableCell align="center">
                                                <Link href={route('Thesis.show', thesis.id)}>
                                                    <Button 
                                                        variant="text" 
                                                        size="small" 
                                                        startIcon={<VisibilityIcon />}
                                                    >
                                                        {t("Ver", "Ver")}
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </TableContainer>
                ) : (
                    <Typography variant="body1" color="text.secondary">
                        {t("Este estudiante no tiene proyectos de tesis asociados.", "Este estudiante no tiene proyectos de tesis asociados.")}
                    </Typography>
                )}
            </div>
        </AdminLayout>
    );
}