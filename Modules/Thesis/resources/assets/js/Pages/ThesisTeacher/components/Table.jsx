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

export default function ThesisTeacherTable({ teachers }) {
    const paperElevation = 5;
    const { t } = useTranslation(["translation"]);

    const handleRowClick = (id) => {
        window.location.href = route("thesisTeacher.show", id);
    };
    
    return (
        <TableContainer component={Paper} elevation={paperElevation} sx={{ mt: 2 }}>
            <Table sx={{ minWidth: { xs: 300, sm: 650 } }} aria-label="simple table">
                <TableHead>
                    <TableRow>
                        <TableCell sx={{ fontWeight: 'bold' }}>{t("ID UC")}</TableCell>
                        <TableCell align="left" sx={{ fontWeight: 'bold' }}>{t("Name")}</TableCell>
                        <TableCell align="left" sx={{ fontWeight: 'bold' }}>{t("Email")}</TableCell>
                        <TableCell align="left" sx={{ fontWeight: 'bold' }}>{t("Tesis Asignadas")}</TableCell>
                        <TableCell align="left"></TableCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {teachers.map((teacher) => (
                        <TableRow
                            key={teacher.id}
                            hover
                            sx={{ cursor: "pointer", "&:last-child td, &:last-child th": { border: 0 } }}
                            onClick={() => handleRowClick(teacher.id)}
                        >
                            <TableCell component="th" scope="row">{teacher.id_uc || 'N/A'}</TableCell>
                            <TableCell align="left">{teacher.name}</TableCell>
                            <TableCell align="left">{teacher.email}</TableCell>
                            <TableCell align="left">
                                {teacher.theses && teacher.theses.length > 0
                                    ? teacher.theses.map(t => t.title).join(', ')
                                    : <span style={{ color: '#aaa' }}>Sin tesis asignadas</span>
                                }
                            </TableCell>
                            <TableCell align="right">
                                <div className="flex justify-end flex-col sm:flex-row gap-2" onClick={e => e.stopPropagation()}>
                                    <Link href={route("thesisTeacher.edit", teacher.id)}>
                                        <Button variant="contained" size="small" startIcon={<EditIcon />}>
                                            {t("Edit")}
                                        </Button>
                                    </Link>
                                    <DeleteDialog teacher={teacher} />
                                </div>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </TableContainer>
    );
}