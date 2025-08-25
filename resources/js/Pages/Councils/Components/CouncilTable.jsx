import React from "react";
import { Link, usePage } from "@inertiajs/react";

import Table from "@mui/material/Table";
import TableBody from "@mui/material/TableBody";
import TableCell from "@mui/material/TableCell";
import TableContainer from "@mui/material/TableContainer";
import TableHead from "@mui/material/TableHead";
import TableRow from "@mui/material/TableRow";
import Paper from "@mui/material/Paper";
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import VisibilityIcon from "@mui/icons-material/Visibility";
import TablePagination from "@mui/material/TablePagination"; // Añadimos paginación de MUI
import TableFooter from "@mui/material/TableFooter";

import DeleteDialog from "./DeleteDialog";

import { useTranslation } from "react-i18next";

// Recibe el objeto paginado 'councils'
export default function CouncilTable({ councils }) {
    const paperElevation = 5;
    const { t } = useTranslation(["translation", "common"]);
    const { auth } = usePage().props; // Obtenemos el usuario autenticado
    const isDirector = auth.user.roles.some(role => role.name === 'director');

    // Función para manejar la paginación con Inertia
    const handleChangePage = (event, newPage) => {
        // Calcula la URL de la página a la que Inertia debe navegar
        const newUrl = councils.links[newPage + 1].url;
        window.location.href = newUrl; // Usar window.location.href para la navegación completa de Inertia
    };

    return (
        <TableContainer
            component={Paper}
            elevation={paperElevation}
            sx={{ mt: 2 }}
        >
            <Table
                sx={{ minWidth: { xs: 300, sm: 650 } }}
                aria-label="councils table"
            >
                <TableHead>
                    <TableRow>
                        <TableCell fontWeight="700">{t("Code")}</TableCell>
                        <TableCell align="left">{t("Name")}</TableCell>
                        <TableCell align="left">{t("Date")}</TableCell>
                        <TableCell align="left">{t("Director")}</TableCell>
                        <TableCell align="center">{t("Participants")}</TableCell>
                        <TableCell align="right">{t("Actions")}</TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {/* Iteramos sobre los datos paginados (councils.data) */}
                    {councils.data.map((council) => (
                        <TableRow
                            key={council.id}
                            sx={{
                                "&:last-child td, &:last-child th": { border: 0 },
                                backgroundColor: council.status === 'Cerrado' ? '#fafafa' : 'inherit',
                                color: council.status === 'Cerrado' ? '#9e9e9e' : 'inherit',
                            }}
                        >
                            <TableCell component="th" scope="row">
                                <Link href={route("councils.show", council.code)}>
                                    {council.code}
                                </Link>
                            </TableCell>
                            <TableCell align="left">{council.name}</TableCell>
                            <TableCell align="left">
                                {/* Usamos el objeto Carbonjs de Laravel, que ya está parseado por Inertia */}
                                {new Date(council.date).toLocaleString('es-ES', {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </TableCell>
                            <TableCell align="left">{council.director.name}</TableCell>
                            <TableCell align="center">{council.participants_count}</TableCell>
                            <TableCell align="right">
                                <div className="flex justify-end flex-col sm:flex-row gap-2">
                                    {/* Link a la página de detalles del consejo */}
                                    <Link href={route("councils.show", council.code)}>
                                        <Button
                                            variant="outlined"
                                            size="small"
                                            startIcon={<VisibilityIcon />}
                                        >
                                            {t("View")}
                                        </Button>
                                    </Link>

                                    {/* Los botones "Editar" solo se muestran si es Director y el consejo no ha sido cerrado */}
                                    {isDirector && council.status !== 'Cerrado' && (
                                        <Link href={route("councils.edit", council.code)}>
                                            <Button variant="contained" size="small" startIcon={<EditIcon />}>
                                                {t("Edit")}
                                            </Button>
                                        </Link>
                                    )}

                                    {/* El botón "Eliminar" solo se muestra si es Director */}
                                    {isDirector && <DeleteDialog council={council} />}
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>

                {/* Footer para la paginación */}
                <TableFooter>
                    <TableRow>
                        <TablePagination
                            rowsPerPageOptions={[]} // Opciones por página, si las hay
                            count={councils.total}
                            rowsPerPage={councils.per_page}
                            page={councils.current_page - 1} // MUI usa índices base 0
                            onPageChange={handleChangePage}
                        />
                    </TableRow>
                </TableFooter>
            </Table>
        </TableContainer>
    );
}