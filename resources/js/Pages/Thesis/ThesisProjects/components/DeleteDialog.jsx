import React from "react";
import { Link } from "@inertiajs/react";
import DeleteIcon from "@mui/icons-material/Delete";
import Button from "@mui/material/Button";
import Dialog from "@mui/material/Dialog";
import DialogActions from "@mui/material/DialogActions";
import DialogContent from "@mui/material/DialogContent";
import DialogContentText from "@mui/material/DialogContentText";
import DialogTitle from "@mui/material/DialogTitle";
import { useTranslation } from "react-i18next";
import WarningAmberIcon from '@mui/icons-material/WarningAmber'; // Importar ícono de advertencia
import { Box } from "@mui/material";
import Typography from "@mui/material/Typography";

export default function DeleteThesisDialog({ thesis }) {
    const [open, setOpen] = React.useState(false);
    const handleClickOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);
    const { t } = useTranslation(["translation", "common"]);

    // Determinar si la tesis está activa para mostrar la advertencia
    const isThesisActive = thesis.is_active;

    return (
        <>
            <Button variant="outlined" startIcon={<DeleteIcon />} size="small" color="error" onClick={handleClickOpen}>
                {t("Delete")}
            </Button>

            <Dialog open={open} onClose={handleClose}>
                <DialogTitle id="alert-dialog-title">
                    {t("¿Desea eliminar el proyecto de tesis?", { statusName: thesis.title })}
                </DialogTitle>
                <DialogContent>
                    <DialogContentText id="alert-dialog-description">
                        {t("Esta acción es irreversible. ¿Esta seguro que desea eliminar?")}
                    </DialogContentText>
                    
                    {isThesisActive && (
                        <Box 
                            sx={{ 
                                mt: 2, 
                                p: 1.5, 
                                backgroundColor: 'warning.light', 
                                color: 'warning.dark',
                                borderRadius: 1,
                                display: 'flex',
                                alignItems: 'center',
                                gap: 1
                            }}
                        >
                            <WarningAmberIcon fontSize="small" />
                            <Typography variant="body2" sx={{ fontWeight: 500 }}>
                                ¡Atención! Esta tesis está activa. Al eliminarla, el estado de los estudiantes asociados será revertido a "inscrito".
                            </Typography>
                        </Box>
                    )}

                </DialogContent>
                <DialogActions>
                    <Button variant="contained" onClick={handleClose}>
                        {t("Cancel")}
                    </Button>
                    <Link href={route("Thesis.destroy", thesis)} method="delete" as="button">
                        <Button component="div" variant="text" color="error" onClick={handleClose}>
                            {t("Delete")}
                        </Button>
                    </Link>
                </DialogActions>
            </Dialog>
        </>
    );
}