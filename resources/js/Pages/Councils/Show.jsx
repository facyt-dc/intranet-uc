import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import React, { useState } from "react";
import { useTranslation } from "react-i18next";

// Componentes de Material-UI
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Paper from '@mui/material/Paper';
import Divider from '@mui/material/Divider';
import Button from '@mui/material/Button';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import Chip from '@mui/material/Chip';
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import GroupIcon from '@mui/icons-material/Group';
import EventIcon from '@mui/icons-material/Event';
import LockClockIcon from '@mui/icons-material/LockClock';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';

// Subcomponentes personalizados
import PointCard from "./Components/PointCard";
import PointForm from "./Components/PointForm";
import Alert from "@/Components/Alert";

// Props: 'council' viene con todas las relaciones cargadas desde el controlador.
// 'auth' es crucial para determinar el rol del usuario.
export default function CouncilShow({ auth, council, counselors, votingOptions }) {
    const { t } = useTranslation(["common", "translation"]);
    const { put, processing } = useForm({});

    const flash = usePage().props.flash || {};

    const isDirector = auth.user.roles.some(role => role.name === 'director');
    const isCouncilOpen = council.status !== 'Cerrado';

    // Estado para el modal de AÑADIR
    const [isModalOpen, setIsModalOpen] = useState(false);
    const handleOpenModal = () => setIsModalOpen(true);
    const handleCloseModal = () => setIsModalOpen(false);

    // estado para el modal de EDITAR
    const [editingPoint, setEditingPoint] = useState(null); // null = cerrado, objeto point = abierto
    const handleOpenEditModal = (point) => setEditingPoint(point);
    const handleCloseEditModal = () => setEditingPoint(null);

    const handleCloseCouncil = () => {
        if (confirm(t('are you sure you want to close this council'))) {
            put(route('councils.close', council.code), {
                preserveScroll: true,
            });
        }
    };

    const getStatusChip = (status) => {
        switch (status) {
            case 'Programado':
                return <Chip label={status} color="info" />;
            case 'Cerrado':
                return <Chip label={status} color="error" />;
            default:
                return <Chip label={status} color="success" />; // Ej: 'En Votación'
        }
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={`${t("council", { count: 1 })}: ${council.name}`} />

            {flash.alert && <Alert message={flash.alert.message} severity={flash.alert.severity} />}

            {/* Encabezado de la Página */}
            <Box className="flex justify-between items-center mb-4">
                <Typography variant="h5" component="h1" className="text-gray-600">
                    {t("council details")}
                </Typography>
                <Link href={route("councils.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large">
                            <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                        </IconButton>
                    </Tooltip>
                </Link>
            </Box>

            {/* Panel de Información Principal del Consejo */}
            <Paper elevation={3} className="p-6 mb-6">
                <Box className="flex justify-between items-start">
                    <div>
                        <Typography variant="h4" component="h2" gutterBottom>
                            {council.name} ({council.code})
                        </Typography>
                        <Box display="flex" alignItems="center" gap={2} color="text.secondary" mb={2}>
                            <Box display="flex" alignItems="center" gap={0.5}><EventIcon fontSize="small" /> {new Date(council.date).toLocaleDateString()}</Box>
                            <Box display="flex" alignItems="center" gap={0.5}><GroupIcon fontSize="small" /> {council.participants.length} {t('Participants')}</Box>
                            {getStatusChip(council.status)}
                        </Box>
                    </div>
                    {isDirector && isCouncilOpen && (
                        <Button variant="contained" color="error" onClick={handleCloseCouncil} disabled={processing} startIcon={<LockClockIcon />}>
                            {t('close council')}
                        </Button>
                    )}
                </Box>
                <Divider className="my-4" />
                <Typography variant="subtitle1" gutterBottom><strong>{t('director')}:</strong> {council.director.name}</Typography>
                <Typography variant="subtitle1"><strong>{t('Participants')}:</strong> {council.participants.map(p => p.name).join(', ')}</Typography>
            </Paper>

            {/* Sección de Puntos del Consejo */}
            <Box>
                <Typography variant="h5" component="h2" className="text-gray-600 mb-4">
                    {t("council points")}
                </Typography>
                <Box className="space-y-4">
                    {council.points.length > 0 ? (
                        council.points.map((point) => (
                            <PointCard
                                key={point.id}
                                point={point}
                                auth={auth}
                                council={council}
                                counselors={counselors}
                                votingOptions={votingOptions}
                                isCouncilOpen={isCouncilOpen}
                                onEdit={() => handleOpenEditModal(point)}
                            />
                        ))
                    ) : (
                        <Typography className="text-center text-gray-500 py-4">{t('no points have been added yet')}</Typography>
                    )}
                </Box>
            </Box>

            {/* Ocultar el botón "Añadir Punto" si el consejo está cerrado */}
            {isDirector && isCouncilOpen && (
                <Box className="mt-6 text-center">
                    <Button variant="contained" onClick={handleOpenModal}>
                        {t('add new point')}
                    </Button>
                </Box>
            )}

            {/* El componente Dialog (Modal) */}
            <Dialog open={isModalOpen} onClose={handleCloseModal} fullWidth maxWidth="md">
                <DialogTitle>{t('add new point')}</DialogTitle>
                <DialogContent>
                    {/* Renderizamos el formulario dentro del modal */}
                    <PointForm
                        council={council}
                        counselors={counselors}
                        votingOptions={votingOptions}
                        onSuccess={handleCloseModal} // Pasamos la función para cerrar el modal
                        onCancel={handleCloseModal}
                    />
                </DialogContent>
            </Dialog>

            {/* Modal para EDITAR */}
            <Dialog open={!!editingPoint} onClose={handleCloseEditModal} fullWidth maxWidth="md">
                <DialogTitle>{t('edit council point')}</DialogTitle>
                <DialogContent>
                    {/* Renderizamos el formulario solo si hay un punto para editar */}
                    {editingPoint && (
                        <PointForm
                            council={council}
                            point={editingPoint} // Pasamos el punto a editar
                            counselors={counselors}
                            votingOptions={votingOptions}
                            onSuccess={handleCloseEditModal}
                            onCancel={handleCloseEditModal}
                        />
                    )}
                </DialogContent>
            </Dialog>
            {/* --- Fin de la lógica del botón y el modal --- */}
        </AdminLayout>
    );
}