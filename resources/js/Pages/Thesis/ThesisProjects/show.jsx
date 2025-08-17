import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link as InertiaLink } from "@inertiajs/react";
import React from "react";
import { useTranslation } from "react-i18next";

// --- IMPORTS ADICIONALES DE MUI ---
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import Typography from "@mui/material/Typography";
import Grid from "@mui/material/Grid";
import Box from '@mui/material/Box';
import Paper from '@mui/material/Paper';
import AttachFileIcon from '@mui/icons-material/AttachFile';
import Chip from '@mui/material/Chip';

export default function ThesisShow({ auth, thesis }) {
    const { t } = useTranslation(["translation", "common"]);

    const ptegFile = thesis.files?.find(f => f.type === 'pteg');
    const tegFile = thesis.files?.find(f => f.type === 'teg');

    // Componente auxiliar para renderizar la información de un archivo
    const FileDisplay = ({ file }) => (
        <Paper variant="outlined" sx={{ p: 1.5, display: 'flex', alignItems: 'center', mt: 1 }}>
            <AttachFileIcon color="action" sx={{ mr: 1.5 }} />
            <Box sx={{ flexGrow: 1, overflow: 'hidden' }}>
                {/* --- ESTA ES LA PARTE CLAVE CORREGIDA --- */}
                {/* Usamos un <a> tag estándar para asegurar una descarga directa */}
                <a 
                    href={route('thesis-files.download', file.id)}
                    style={{ textDecoration: 'none', color: 'inherit' }}
                >
                    <Typography 
                        variant="body2" 
                        noWrap 
                        sx={{ '&:hover': { textDecoration: 'underline', cursor: 'pointer' } }}
                    >
                        {file.original_name}
                    </Typography>
                </a>
            </Box>
        </Paper>
    );

    const getStatusChipColor = (status) => {
        if (status){
            return 'success';
        }
        else{
            return 'default';
        }
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={t("Detalle de Tesis", "Detalle de Tesis")} />
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">
                    {t("Detalles de la tesis", "Detalles de la tesis")}
                </h2>
                <div className="flex items-center gap-4">

                    <Chip 
                        label={thesis.is_active ? 'Activo' : 'Inactivo'}
                        color={getStatusChipColor(thesis.is_active)}
                    />

                    <InertiaLink href={route("Thesis.edit", thesis.id)}>
                        <Button variant="contained" startIcon={<EditIcon />}>
                            {t("Editar", "Editar")}
                        </Button>
                    </InertiaLink>
                    <InertiaLink href={route("Thesis.index")}>
                        <Button variant="outlined">
                            {t("Volver", "Volver")}
                        </Button>
                    </InertiaLink>
                </div>
            </div>

            {/* ... (El resto del componente sigue igual) ... */}
            <Grid container spacing={3}>
                <Grid item xs={12} sm={6} md={4}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("Titulo", "Titulo")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">{thesis.title}</Typography>
                </Grid>
                <Grid item xs={12} sm={6} md={4}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        {t("Fecha", "Fecha")}
                    </Typography>
                    <Typography variant="body1" className="font-medium">{new Date(thesis.date).toLocaleDateString()}</Typography>
                </Grid>
                <Grid item xs={12} md={4}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>{t("Tesistas Asignados", "Tesistas Asignados")}</Typography>
                    {thesis.students && thesis.students.length > 0 ? (
                        thesis.students.map((student) => (
                            <Box key={student.id} sx={{ mb: 1.5 }}>
                                <Typography variant="body1" className="font-medium">{student.name}</Typography>
                                <Typography variant="body2" color="text.secondary">C.I: {student.ci || 'N/A'} | ID UC: {student.id_uc || 'N/A'}</Typography>
                            </Box>
                        ))
                    ) : (
                        <Typography variant="body1" className="italic">{t("No hay tesistas asignados.", "No hay tesistas asignados.")}</Typography>
                    )}
                </Grid>
            </Grid>

            <Box mt={5}>
                <Typography variant="h6" gutterBottom>
                    {t("Documentos Adjuntos", "Documentos Adjuntos")}
                </Typography>
                <Grid container spacing={3}>
                    <Grid item xs={12} md={6}>
                        <Typography variant="subtitle2" color="text.secondary">
                            Documento PTEG
                        </Typography>
                        {ptegFile ? (
                            <FileDisplay file={ptegFile} />
                        ) : (
                            <Typography variant="body2" color="text.secondary" sx={{ mt: 1, fontStyle: 'italic' }}>
                                No se ha subido un documento PTEG.
                            </Typography>
                        )}
                    </Grid>

                    <Grid item xs={12} md={6}>
                        <Typography variant="subtitle2" color="text.secondary">
                            Documento TEG
                        </Typography>
                        {tegFile ? (
                            <FileDisplay file={tegFile} />
                        ) : (
                            <Typography variant="body2" color="text.secondary" sx={{ mt: 1, fontStyle: 'italic' }}>
                                No se ha subido un documento TEG.
                            </Typography>
                        )}
                    </Grid>
                </Grid>
            </Box>
        </AdminLayout>
    );
}