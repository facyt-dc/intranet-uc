import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import DeleteIcon from '@mui/icons-material/Delete';

// Recibe el 'agenda' y el 'point' como props para construir la ruta de eliminación
export default function DeletePointDialog({ agenda, point }) {
    const [open, setOpen] = useState(false);
    const { t } = useTranslation(['common']);
    const { delete: inertiaDelete, processing } = useForm();

    const handleClickOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    const handleDelete = () => {
        inertiaDelete(route('points.destroy', point.id), {
            preserveScroll: true,
            onSuccess: () => handleClose(),
        });
    };

    return (
        <>
            <Tooltip title="Eliminar Punto">
                <IconButton size="small" color="error" onClick={handleClickOpen}>
                    <DeleteIcon />
                </IconButton>
            </Tooltip>
            <Dialog open={open} onClose={handleClose}>
                <DialogTitle>{t('confirm delete', { field: t('agenda point') })}</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                        {t('are you sure you want to delete this point')}? "{point.description}"
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose} color="secondary">{t('button.cancel')}</Button>
                    <Button onClick={handleDelete} color="error" variant="contained" disabled={processing} autoFocus>
                        {t('button.delete')}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}

  