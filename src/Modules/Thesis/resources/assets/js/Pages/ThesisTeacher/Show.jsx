import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";
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
import Chip from '@mui/material/Chip';

export default function ThesisTeacherShow({ auth, teacher }) {
    const { t } = useTranslation(["common"]);

    return (
        <AdminLayout auth={auth}>
            <Head title={`${t("Detalle Docente")}: ${teacher.name}`} />
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">{t("Detalle del Docente")}</h2>
                <div className="flex items-center gap-4">
                    <Link href={route("thesisTeacher.edit", teacher.id)}>
                        <Button variant="contained" startIcon={<EditIcon />}>{t("Editar")}</Button>
                    </Link>
                    <Link href={route("thesisTeacher.index")}>
                        <Button variant="outlined">{t("Volver")}</Button>
                    </Link>
                </div>
            </div>

            <Paper sx={{ p: 3 }}>
                <Grid container spacing={3}>
                    <Grid item xs={12} sm={6}><Typography variant="subtitle2" color="text.secondary">Nombre</Typography><Typography variant="body1">{teacher.name}</Typography></Grid>
                    <Grid item xs={12} sm={6}><Typography variant="subtitle2" color="text.secondary">Email</Typography><Typography variant="body1">{teacher.email}</Typography></Grid>
                    <Grid item xs={12} sm={6}><Typography variant="subtitle2" color="text.secondary">Cédula</Typography><Typography variant="body1">{teacher.ci}</Typography></Grid>
                    <Grid item xs={12} sm={6}><Typography variant="subtitle2" color="text.secondary">ID UC</Typography><Typography variant="body1">{teacher.id_uc || 'N/A'}</Typography></Grid>
                </Grid>
            </Paper>

            <div className="mt-8">
                <Typography variant="h6" gutterBottom>{t("Proyectos de Tesis Asignados")}</Typography>
                {teacher.theses && teacher.theses.length > 0 ? (
                    <TableContainer component={Paper}>
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableCell sx={{ fontWeight: 'bold' }}>{t("Título")}</TableCell>
                                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>{t("Fecha")}</TableCell>
                                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>{t("Estatus")}</TableCell>
                                    <TableCell align="center" sx={{ fontWeight: 'bold' }}>{t("Acciones")}</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {teacher.theses.map((thesis) => (
                                    <TableRow key={thesis.id}>
                                        <TableCell>{thesis.title}</TableCell>
                                        <TableCell align="right">{new Date(thesis.date).toLocaleDateString()}</TableCell>
                                        <TableCell align="right"><Chip label={thesis.is_active ? 'Activo' : 'Inactivo'} color={thesis.is_active ? 'success' : 'default'} size="small" /></TableCell>
                                        <TableCell align="center">
                                            <Link href={route('Thesis.show', thesis.id)}>
                                                <Button variant="text" size="small" startIcon={<VisibilityIcon />}>{t("Ver")}</Button>
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>
                ) : (
                    <Typography variant="body1" color="text.secondary">{t("Este docente no tiene proyectos de tesis asignados.")}</Typography>
                )}
            </div>
        </AdminLayout>
    );
}