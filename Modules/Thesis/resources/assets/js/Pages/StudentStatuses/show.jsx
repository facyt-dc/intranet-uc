import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";
import { useTranslation } from "react-i18next";

// MUI Imports
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import Typography from "@mui/material/Typography";
import Grid from "@mui/material/Grid";
import Paper from "@mui/material/Paper";

export default function StudentStatusShow({ auth, studentStatus }) {
    const { t } = useTranslation(["translation", "common"]);

    return (
        <AdminLayout auth={auth}>
            <Head title={t("Detalle de Estatus", "Detalle de Estatus")} />
            
            {/* Cabecera de la página */}
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">
                    {t("Detalles del Estatus", "Detalles del Estatus")}
                </h2>
                <div className="flex items-center gap-2">
                    <Link href={route("studentStatuses.edit", studentStatus.id)}>
                        <Button
                            variant="contained"
                            startIcon={<EditIcon />}
                        >
                            {t("Editar", "Editar")}
                        </Button>
                    </Link>
                    <Link href={route("studentStatuses.index")}>
                        <Button variant="outlined">
                            {t("Volver", "Volver")}
                        </Button>
                    </Link>
                </div>
            </div>

            {/* Contenido con los detalles */}
                <Grid container spacing={3}>
                    {/* Detalle: Nombre */}
                    <Grid item xs={12} md={6}>
                        <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                            {t("Nombre", "Nombre")}
                        </Typography>
                        <Typography variant="body1" className="font-medium">
                            {studentStatus.name}
                        </Typography>
                    </Grid>

                    {/* Detalle: Descripción */}
                    <Grid item xs={12} md={6}>
                        <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                            {t("Descripción", "Descripción")}
                        </Typography>
                        <Typography variant="body1" className="font-medium" style={{ whiteSpace: 'pre-wrap' }}>
                            {studentStatus.description || 'N/A'}
                        </Typography>
                    </Grid>
                </Grid>
        </AdminLayout>
    );
}