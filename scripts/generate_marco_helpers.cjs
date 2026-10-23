/**
 * Generador Automatizado del Marco Tecnológico para Tesis de Grado — FusaShop
 * Genera un archivo Word (.docx) formal con especificación completa, tablas,
 * diagramas arquitectónicos textuales y estándares APA 7ma edición.
 */

const fs = require('fs');
const path = require('path');
const {
  Document,
  Packer,
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  HeadingLevel,
  AlignmentType,
  WidthType,
  BorderStyle,
  ShadingType,
  PageNumber,
  NumberFormat,
  Footer,
  Header
} = require('docx');

console.log('Iniciando generador del Marco Tecnológico para Tesis FusaShop...');

// Paleta de colores institucionales FusaShop
const COLORS = {
  PRIMARY: '006C47',       // Verde institucional oscuro
  PRIMARY_LIGHT: '10B981', // Verde esmeralda vivo
  SECONDARY: 'F97316',     // Naranja Sumapaz
  DARK_TEXT: '1E293B',     // Slate oscuro para textos
  MUTED_TEXT: '64748B',    // Gris neutro
  LIGHT_BG: 'F8FAFC',      // Fondo tenue
  CALLOUT_BG: 'F0FDF4',    // Fondo verde claro para notas
  BORDER_COLOR: 'CBD5E1',  // Borde estándar
  TABLE_HEADER: '006C47',  // Fondo encabezado tabla
  WHITE: 'FFFFFF'
};

// Estilos de bordes
const noBorder = {
  top: { style: BorderStyle.NONE, size: 0, color: 'auto' },
  bottom: { style: BorderStyle.NONE, size: 0, color: 'auto' },
  left: { style: BorderStyle.NONE, size: 0, color: 'auto' },
  right: { style: BorderStyle.NONE, size: 0, color: 'auto' }
};

const cellBorder = {
  top: { style: BorderStyle.SINGLE, size: 4, color: COLORS.BORDER_COLOR },
  bottom: { style: BorderStyle.SINGLE, size: 4, color: COLORS.BORDER_COLOR },
  left: { style: BorderStyle.SINGLE, size: 4, color: COLORS.BORDER_COLOR },
  right: { style: BorderStyle.SINGLE, size: 4, color: COLORS.BORDER_COLOR }
};

// Helpers de construcción de párrafos y títulos
function p(text, options = {}) {
  const runs = Array.isArray(text) ? text : [new TextRun({ text, font: 'Calibri', size: 22, color: COLORS.DARK_TEXT, ...options })];
  return new Paragraph({
    children: runs,
    spacing: { after: options.after !== undefined ? options.after : 120, line: 276 },
    alignment: options.alignment || AlignmentType.LEFT
  });
}

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    children: [
      new TextRun({
        text,
        bold: true,
        font: 'Calibri',
        size: 32,
        color: COLORS.PRIMARY
      })
    ],
    spacing: { before: 360, after: 180 }
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    children: [
      new TextRun({
        text,
        bold: true,
        font: 'Calibri',
        size: 26,
        color: COLORS.DARK_TEXT
      })
    ],
    spacing: { before: 280, after: 140 }
  });
}

function h3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    children: [
      new TextRun({
        text,
        bold: true,
        font: 'Calibri',
        size: 23,
        color: COLORS.PRIMARY
      })
    ],
    spacing: { before: 220, after: 100 }
  });
}

function bullet(boldPrefix, normalText) {
  return new Paragraph({
    bullet: { level: 0 },
    spacing: { after: 100, line: 276 },
    children: [
      new TextRun({ text: boldPrefix + ': ', bold: true, font: 'Calibri', size: 22, color: COLORS.DARK_TEXT }),
      new TextRun({ text: normalText, font: 'Calibri', size: 22, color: COLORS.DARK_TEXT })
    ]
  });
}

function callout(title, text) {
  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: {
      top: BorderStyle.NONE,
      bottom: BorderStyle.NONE,
      right: BorderStyle.NONE,
      left: { style: BorderStyle.SINGLE, size: 24, color: COLORS.PRIMARY }
    },
    rows: [
      new TableRow({
        children: [
          new TableCell({
            width: { size: 100, type: WidthType.PERCENTAGE },
            shading: { fill: COLORS.CALLOUT_BG, type: ShadingType.CLEAR },
            margins: { top: 140, bottom: 140, left: 180, right: 180 },
            children: [
              new Paragraph({
                children: [
                  new TextRun({ text: '💡 ' + title, bold: true, font: 'Calibri', size: 22, color: COLORS.PRIMARY })
                ],
                spacing: { after: 80 }
              }),
              new Paragraph({
                children: [
                  new TextRun({ text, font: 'Calibri', size: 21, color: COLORS.DARK_TEXT })
                ],
                spacing: { after: 40, line: 260 }
              })
            ]
          })
        ]
      })
    ]
  });
}

function codeBox(codeContent) {
  const lines = codeContent.split('\n');
  const paragraphs = lines.map(line => new Paragraph({
    children: [
      new TextRun({ text: line, font: 'Consolas', size: 19, color: '0F172A' })
    ],
    spacing: { after: 40, line: 220 }
  }));

  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: cellBorder,
    rows: [
      new TableRow({
        children: [
          new TableCell({
            width: { size: 100, type: WidthType.PERCENTAGE },
            shading: { fill: 'F1F5F9', type: ShadingType.CLEAR },
            margins: { top: 120, bottom: 120, left: 160, right: 160 },
            children: paragraphs
          })
        ]
      })
    ]
  });
}

function makeTable(headers, rowsData, colWidths = []) {
  const headerRow = new TableRow({
    tableHeader: true,
    children: headers.map((h, i) => new TableCell({
      width: colWidths[i] ? { size: colWidths[i], type: WidthType.PERCENTAGE } : undefined,
      shading: { fill: COLORS.TABLE_HEADER, type: ShadingType.CLEAR },
      margins: { top: 120, bottom: 120, left: 140, right: 140 },
      borders: cellBorder,
      children: [
        new Paragraph({
          children: [new TextRun({ text: h, bold: true, font: 'Calibri', size: 21, color: COLORS.WHITE })],
          alignment: AlignmentType.CENTER
        })
      ]
    }))
  });

  const bodyRows = rowsData.map((row, rIndex) => new TableRow({
    children: row.map((cellText, cIndex) => new TableCell({
      width: colWidths[cIndex] ? { size: colWidths[cIndex], type: WidthType.PERCENTAGE } : undefined,
      shading: { fill: rIndex % 2 === 1 ? COLORS.LIGHT_BG : COLORS.WHITE, type: ShadingType.CLEAR },
      margins: { top: 100, bottom: 100, left: 140, right: 140 },
      borders: cellBorder,
      children: [
        new Paragraph({
          children: [new TextRun({ text: cellText, font: 'Calibri', size: 20, color: COLORS.DARK_TEXT })],
          alignment: cIndex === 0 ? AlignmentType.LEFT : AlignmentType.LEFT
        })
      ]
    }))
  }));

  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: cellBorder,
    rows: [headerRow, ...bodyRows]
  });
}

module.exports = {
  p, h1, h2, h3, bullet, callout, codeBox, makeTable, COLORS
};
