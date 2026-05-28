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

export default function DeleteVotingOptionDialog({ option }) {
    const [open, setOpen] = useState(false);
    const { t } = useTranslation(['common']);
    const { delete: inertiaDelete, processing } = useForm();

    const handleClickOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

    // Verifica si la opción está siendo utilizada en puntos de votación
    const isBeingUsed = option.points_count > 0;

    const handleDelete = () => {
        inertiaDelete(route('settings.voting-options.destroy', option.id), {
            preserveScroll: true,
            onSuccess: () => handleClose(),
        });
    };

    return (
        <>
            <Tooltip title={isBeingUsed ? 'No se puede eliminar, está en uso por un punto' :t('button.delete')}>
                <IconButton color="error" onClick={handleClickOpen} disabled={isBeingUsed}>
                    <DeleteIcon />
                </IconButton>
            </Tooltip>
            <Dialog open={open} onClose={handleClose}>
                <DialogTitle>{t('confirm delete', { field: t('option') })}</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                        {t('are you sure you want to delete this field', { field: option.name })}?
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