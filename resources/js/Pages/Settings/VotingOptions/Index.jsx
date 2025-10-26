import React, { useState } from 'react';
import { Head, usePage, useForm, router} from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

// Layout y Componentes Comunes
import AdminLayout from '@/Layouts/AdminLayout';
import Alert from '@/Components/Alert';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Typography from '@mui/material/Typography';
import Switch from '@mui/material/Switch';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';

import AddRoundedIcon from '@mui/icons-material/AddRounded';
import EditIcon from '@mui/icons-material/Edit';

// Subcomponentes para esta página
import VotingOptionFormModal from './Components/VotingOptionFormModal';
import DeleteVotingOptionDialog from './Components/DeleteVotingOptionDialog';

export default function Index({ auth, votingOptions }) {
    const { t } = useTranslation(['common', 'agenda']);
    const { flash } = usePage().props;
    const alert = flash?.alert;

    // Estado para controlar el modal de creación/edición
    const [modalState, setModalState] = useState({ open: false, option: null });
    
    // Estado y hook para manejar la actualización del Switch (is_active)
    const [isSwitchProcessing, setIsSwitchProcessing] = useState(false);
    const [updatingSwitchId, setUpdatingSwitchId] = useState(null);

    const handleOpenCreateModal = () => setModalState({ open: true, option: null });
    const handleOpenEditModal = (option) => setModalState({ open: true, option });
    const handleCloseModal = () => setModalState({ open: false, option: null });

    const handleToggleActive = (option) => {
        setUpdatingSwitchId(option.id);
        setIsSwitchProcessing(true); // Inicia el estado de carga manualmente

        // router.put(url, data, options)
        router.put(route('settings.voting-options.update', option.id), {
            // Aquí se pasan los datos que SÍ se enviarán
            name: option.name,
            is_active: !option.is_active,
        }, {
            preserveScroll: true,
            onFinish: () => {
                // Finaliza el estado de carga manualmente
                setUpdatingSwitchId(null);
                setIsSwitchProcessing(false);
            },
        });
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={t('agenda:Voting options')} />
            {alert && <Alert message={alert.message} severity={alert.severity} />}

            <Box className="flex justify-between items-center mb-4">
                <Typography variant="h5" className="text-gray-600">
                    {t('agenda:Manage voting options')}
                </Typography>
                <Button variant="contained" startIcon={<AddRoundedIcon />} onClick={handleOpenCreateModal}>
                    {t('button.create field', { field: t('Option') })}
                </Button>
            </Box>

            <TableContainer component={Paper} elevation={3}>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell sx={{ fontWeight: 'bold' }}>{t('Name')}</TableCell>
                            <TableCell sx={{ fontWeight: 'bold' }} align="center">{t('Status')}</TableCell>
                            <TableCell sx={{ fontWeight: 'bold' }} align="right">{t('Actions')}</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {votingOptions.map((option) => (
                            <TableRow key={option.id} hover>
                                <TableCell>{option.name}</TableCell>
                                <TableCell align="center">
                                    <Tooltip title={option.is_active ? t('Active') : t('Inactive')}>
                                        <Switch
                                            checked={option.is_active}
                                            onChange={() => handleToggleActive(option)}
                                            disabled={isSwitchProcessing && updatingSwitchId === option.id}
                                        />
                                    </Tooltip>
                                </TableCell>
                                <TableCell align="right">
                                    <Tooltip title={t('Edit')}>
                                        <IconButton onClick={() => handleOpenEditModal(option)}>
                                            <EditIcon />
                                        </IconButton>
                                    </Tooltip>
                                    <DeleteVotingOptionDialog option={option} />
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>

            <VotingOptionFormModal
                open={modalState.open}
                option={modalState.option}
                onClose={handleCloseModal}
            />
        </AdminLayout>
    );
}