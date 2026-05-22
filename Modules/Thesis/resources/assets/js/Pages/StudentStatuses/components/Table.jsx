import React from "react";
import { Link } from "@inertiajs/react";

import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Paper from "@mui/material/Paper";
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";

import DeleteDialog from "./DeleteDialog";
import { useTranslation } from "react-i18next";

export default function StatusTable({ studentStatuses }) {
    const paperElevation = 5;
    const { t } = useTranslation(["translation"]);

    // --- FUNCIÓN PARA MANEJAR EL CLICK EN LA FILA ---
    const handleRowClick = (id) => {
        // Redirige a la nueva ruta 'show' para el estatus
        window.location.href = route("studentStatuses.show", id);
    };

    return (
        <TableContainer
            component={Paper}
            elevation={paperElevation}
            sx={{ mt: 2 }}
        >
            <Table sx={{ minWidth: { xs: 300, sm: 650 } }} aria-label="simple table">
                <TableHead>
                    <TableRow>
                        <TableCell sx={{ fontWeight: 'bold' }}>{t("ID")}</TableCell>
                        <TableCell align="left" sx={{ fontWeight: 'bold' }}>{t("Name")}</TableCell>
                        <TableCell align="left" sx={{ fontWeight: 'bold' }}>{t("Description")}</TableCell>
                        <TableCell align="left"></TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {studentStatuses.map((status) => (
                        <TableRow
                            key={status.id}
                            hover // <-- Añade efecto hover
                            sx={{
                                cursor: "pointer", // <-- Cambia el cursor a una mano
                                "&:last-child td, &:last-child th": { border: 0 },
                            }}
                            onClick={() => handleRowClick(status.id)} // <-- Llama a la función al hacer click
                        >
                            <TableCell component="th" scope="row">
                                {status.id}
                            </TableCell>
                            <TableCell align="left">{status.name}</TableCell>
                            <TableCell align="left">{status.description}</TableCell>
                            <TableCell align="right">
                                {/* Detenemos la propagación para que el click en los botones no active el click de la fila */}
                                <div 
                                    className="flex justify-end flex-col sm:flex-row gap-2"
                                    onClick={e => e.stopPropagation()} 
                                >
                                    <Link href={route("studentStatuses.edit", status)}>
                                        <Button
                                            variant="contained"
                                            size="small"
                                            startIcon={<EditIcon />}
                                        >
                                            {t("Edit")}
                                        </Button>
                                    </Link>
                                    <DeleteDialog studentStatus={status} />
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </TableContainer>
    );
}