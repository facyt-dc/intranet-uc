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
import Chip from '@mui/material/Chip';

export default function ThesisTable({ thesis }) {
    const paperElevation = 5;
    console.log(thesis);
    const { t } = useTranslation(["translation"]);

        const handleRowClick = (id) => {
        window.location.href = route("Thesis.show", id);

    };


    return (
        <TableContainer
            component={Paper}
            elevation={paperElevation}
            sx={{ mt: 2 }}
        >
            <Table
                sx={{ minWidth: { xs: 300, sm: 650 } }}
                aria-label="simple table"
            >
               <TableHead>
        <TableRow>
        <TableCell fontWeight="700">{t("ID")}</TableCell>
        <TableCell align="left">{t("Title")}</TableCell>
        <TableCell align="left">{t("Date")}</TableCell>
        <TableCell align="left">{t("Estudiantes")}</TableCell>
        <TableCell align="left">{t("Status")}</TableCell>

        <TableCell align="left"></TableCell>
    </TableRow>
</TableHead>
<TableBody>
    {thesis.map((thesisItem) => (
    <TableRow
                    key={thesisItem.id}
                    hover
                    sx={{
                        cursor: "pointer",
                        "&:last-child td, &:last-child th": { border: 0 },
                    }}
                    onClick={() => handleRowClick(thesisItem.id)}
                >
            <TableCell component="th" scope="row">
                {thesisItem.id}
            </TableCell>
            <TableCell align="left">{thesisItem.title}</TableCell>
            <TableCell align="left">{thesisItem.date}</TableCell>
            <TableCell align="left">
                {thesisItem.students && thesisItem.students.length > 0
                    ? thesisItem.students.map(s => `${s.name} (${s.id_uc})`).join(', ')
                    : <span style={{ color: '#aaa' }}>Sin estudiantes</span>
                }
            </TableCell>

            <TableCell align="left">
                <Chip 
                    label={thesisItem.is_active ? 'Activo' : 'Inactivo'}
                    color={thesisItem.is_active ? 'success' : 'default'}
                    size="small"
                />
            </TableCell>
            <TableCell align="right">
                <div className="flex justify-end flex-col sm:flex-row gap-2"
                onClick={e => e.stopPropagation()} // Evita que el click en los botones dispare el click de la fila
                >   
                    <Link href={route("Thesis.edit", thesisItem)}>
                        <Button
                            variant="contained"
                            size="small"
                            startIcon={<EditIcon />}
                        >
                            {t("Edit")}
                        </Button>
                    </Link>
                    <DeleteDialog thesis={thesisItem} />
                </div>
            </TableCell>
        </TableRow>
    ))}
</TableBody>
            </Table>
        </TableContainer>
    );
}
