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

export default function DeleteTeacherDialog({ teacher }) {
    const [open, setOpen] = React.useState(false);
    const handleClickOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);
    const { t } = useTranslation(["common"]);

    return (
        <>
            <Button variant="outlined" startIcon={<DeleteIcon />} size="small" color="error" onClick={handleClickOpen}>
                {t("Delete")}
            </Button>
            <Dialog open={open} onClose={handleClose}>
                <DialogTitle>{t("¿Desea eliminar al docente?")} {teacher.name}?</DialogTitle>
                <DialogContent>
                    <DialogContentText>{t("Esta acción es irreversible.")}</DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button variant="contained" onClick={handleClose}>{t("Cancel")}</Button>
                    <Link href={route("thesisTeacher.destroy", teacher.id)} method="delete" as="button">
                        <Button component="div" variant="text" color="error" onClick={handleClose}>{t("Delete")}</Button>
                    </Link>
                </DialogActions>
            </Dialog>
        </>
    );
}