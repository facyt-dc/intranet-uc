import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";

// --- TUS IMPORTS ORIGINALES ---
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import Form from "./components/Form"; // Asumiendo que tu Form está en ./components/

// --- NUEVOS IMPORTS PARA LOS ELEMENTOS VISUALES (de show.jsx) ---
import Typography from "@mui/material/Typography";
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
import Button from "@mui/material/Button";
// --- FIN DE NUEVOS IMPORTS ---


export default function ThesisStudentEdit({ auth, thesisStudent, statuses  }) {
    const { t } = useTranslation(["translation", "common"]);
    
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


    const getStatusChipColor = (statusName) => {
        if (!statusName) return 'default';
        const name = statusName.toLowerCase();
        
        switch (name) {
            case 'inscrito': return 'success';
            case 'pteg inscrito': return 'primary';
            case 'retirado': return 'error';
            case 'teg inscrito': return 'warning';
            default: return 'default';
        }
    };
    // --- FIN DE LÓGICA COPIADA ---

    return (
        <AdminLayout auth={auth}>
            <Head
                title={t("Edit resource", {
                    resource: t("thesis student", {
                        count: 1,
                        ns: "common",
                    }),
                })}
            />
            
            {/* CABECERA ADAPTADA */}
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">
                    {t("Edit resource", {
                        resource: t("thesis student", {
                            count: 1,
                            ns: "common",
                        }),
                    })}: {thesisStudent.name}
                </h2>
                
                <div className="flex items-center gap-2"> {/* Reducido el gap para que se vea bien con el IconButton */}
                    {/* Chip de estatus (solo visual) */}
                    {thesisStudent.status && (
                        <Chip 
                            label={thesisStudent.status.name} 
                            color={getStatusChipColor(thesisStudent.status.name)}
                        />
                    )}
                    
                    {/* Tu botón original de "volver" */}
                    <Link href={route("thesisStudent.index")}>
                        <Tooltip title={t("button.go back", { ns: "common" })}>
                            <IconButton size="large">
                                <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                            </IconButton>
                        </Tooltip>
                    </Link>
                </div>
            </div>

            {/* FORMULARIO ENVUELTO EN UN PAPER PARA MEJOR UI */}
            <Paper sx={{ p: 3, mb: 4 }}>
                <Typography variant="h6" gutterBottom>
                    {t("Datos del Tesista", "Datos del Tesista")}
                </Typography>
                <Form
                    thesisStudent={thesisStudent}
                    statuses={statuses} 
                    method="patch"
                    routeName="thesisStudent.update"
                />
                
            </Paper>

            {/* SECCIÓN DE TABLA DE TESIS (INFORMATIVA) */}
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