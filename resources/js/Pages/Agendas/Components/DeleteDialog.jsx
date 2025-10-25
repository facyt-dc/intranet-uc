import React from 'react';
import { useForm } from '@inertiajs/react';
import Button from '@mui/material/Button';
import DeleteIcon from '@mui/icons-material/Delete';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import { useTranslation } from 'react-i18next';

export default function DeleteDialog({ agenda }) {
    const [open, setOpen] = React.useState(false);
    const { t } = useTranslation(['common']);
    const { delete: inertiaDelete } = useForm();

    const handleClickOpen = () => {
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
    };

    const handleDelete = () => {
        inertiaDelete(route('agendas.destroy', agenda.code), {
            onSuccess: () => handleClose(),
        });
    };

    return (
        <>
            <Button
                variant="outlined"
                color="error"
                size="small"
                startIcon={<DeleteIcon />}
                onClick={handleClickOpen}
            >
                {t('button.delete')}
            </Button>
            <Dialog
                open={open}
                onClose={handleClose}
                aria-labelledby="alert-dialog-title"
                aria-describedby="alert-dialog-description"
            >
                <DialogTitle id="alert-dialog-title">
                    {t('confirm delete', { field: t('agenda', { count: 1 }) })}
                </DialogTitle>
                <DialogContent>
                    <DialogContentText id="alert-dialog-description">
                        {t('are you sure you want to delete this field', { field: agenda.name })}?
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose} color="primary" variant="outlined">
                        {t('button.cancel')}
                    </Button>
                    <Button onClick={handleDelete} color="error" variant="contained" autoFocus>
                        {t('button.delete')}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}