from docx import Document
from docx.text.paragraph import Paragraph
from docx.table import Table as DocxTable
from docx.oxml.text.paragraph import CT_P
from docx.oxml.table import CT_Tbl
from reportlab.lib import colors
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_RIGHT
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph as RPara, Spacer, Table, TableStyle, Image, PageBreak
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfbase import pdfmetrics
from pathlib import Path
import re

base=Path(__file__).parent
src=base/'Plan_de_Pruebas_FusaShop.docx'; out=base/'render'/'Plan_QA.pdf'; out.parent.mkdir(exist_ok=True)
doc=Document(src)
styles=getSampleStyleSheet()
styles.add(ParagraphStyle(name='BodyX',parent=styles['BodyText'],fontName='Helvetica',fontSize=8.4,leading=11,spaceAfter=4,textColor=colors.HexColor('#19342B')))
styles.add(ParagraphStyle(name='TitleX',parent=styles['Title'],fontName='Helvetica-Bold',fontSize=23,leading=27,textColor=colors.HexColor('#07563F'),spaceAfter=8))
styles.add(ParagraphStyle(name='H1X',parent=styles['Heading1'],fontName='Helvetica-Bold',fontSize=15,leading=18,textColor=colors.HexColor('#07563F'),spaceBefore=11,spaceAfter=5,keepWithNext=True))
styles.add(ParagraphStyle(name='H2X',parent=styles['Heading2'],fontName='Helvetica-Bold',fontSize=10.5,leading=13,textColor=colors.HexColor('#087B55'),spaceBefore=8,spaceAfter=4,keepWithNext=True))
styles.add(ParagraphStyle(name='H3X',parent=styles['Heading3'],fontName='Helvetica-Bold',fontSize=9.5,leading=12,textColor=colors.HexColor('#07563F'),spaceBefore=5,spaceAfter=3,keepWithNext=True))
styles.add(ParagraphStyle(name='CellX',parent=styles['BodyText'],fontName='Helvetica',fontSize=6.4,leading=8,textColor=colors.HexColor('#19342B')))
styles.add(ParagraphStyle(name='CellHeadX',parent=styles['BodyText'],fontName='Helvetica-Bold',fontSize=6.5,leading=8,textColor=colors.white))
styles.add(ParagraphStyle(name='ListX',parent=styles['BodyText'],fontName='Helvetica',fontSize=8.3,leading=10.5,leftIndent=13,firstLineIndent=-9,spaceAfter=2,textColor=colors.HexColor('#19342B')))
def fmt(s):
    s=s.replace('&','&amp;').replace('<','&lt;').replace('>','&gt;')
    return s.replace('\n','<br/>')
def story_table(tb):
    data=[]
    for ri,row in enumerate(tb.rows):
        vals=[]
        for cell in row.cells:
            txt='\n'.join(p.text for p in cell.paragraphs)
            vals.append(RPara(fmt(txt),styles['CellHeadX'] if ri==0 else styles['CellX']))
        data.append(vals)
    n=len(data[0]); avail=7.05*inch
    # Preserve proportional widths from Word when present; otherwise distribute.
    widths=[]
    try:
        widths=[c.width/914400*inch for c in tb.rows[0].cells]
        scale=avail/sum(widths); widths=[w*scale for w in widths]
    except Exception: widths=[avail/n]*n
    t=Table(data,colWidths=widths,repeatRows=1,hAlign='CENTER')
    ts=[('BACKGROUND',(0,0),(-1,0),colors.HexColor('#07563F')),('GRID',(0,0),(-1,-1),.35,colors.HexColor('#B7C7BF')),('VALIGN',(0,0),(-1,-1),'MIDDLE'),('LEFTPADDING',(0,0),(-1,-1),4),('RIGHTPADDING',(0,0),(-1,-1),4),('TOPPADDING',(0,0),(-1,-1),4),('BOTTOMPADDING',(0,0),(-1,-1),4)]
    for i in range(1,len(data)):
        if i%2==0: ts.append(('BACKGROUND',(0,i),(-1,i),colors.HexColor('#F3F5F4')))
    t.setStyle(TableStyle(ts)); return t
story=[]
for child in doc.element.body.iterchildren():
    if isinstance(child,CT_P):
        p=Paragraph(child,doc)
        txt=p.text.strip()
        if not txt: continue
        # Cover logo paragraph rendered above the body heading.
        if p._p.xpath('.//pic:pic'):
            img=base.parent.parent/'public'/'logo.png'
            if img.exists(): story.append(Image(str(img),width=1*inch,height=.58*inch))
            continue
        sty=p.style.name if p.style else ''
        if sty=='Title': s=styles['TitleX']
        elif sty=='Heading 1': s=styles['H1X']
        elif sty=='Heading 2': s=styles['H2X']
        elif sty=='Heading 3': s=styles['H3X']
        elif sty=='List Bullet': s=styles['ListX']; txt='•  '+txt
        elif sty=='List Number': s=styles['ListX']; txt='–  '+txt
        else: s=styles['BodyX']
        story.append(RPara(fmt(txt),s))
    elif isinstance(child,CT_Tbl):
        tb=DocxTable(child,doc)
        story.append(story_table(tb)); story.append(Spacer(1,5))
def page(canvas,docobj):
    canvas.saveState(); w,h=letter
    canvas.setFont('Helvetica-Bold',7); canvas.setFillColor(colors.HexColor('#087B55')); canvas.drawRightString(w-.78*inch,h-.35*inch,'FUSASHOP  |  CALIDAD Y SEGURIDAD')
    canvas.setFont('Helvetica',7); canvas.setFillColor(colors.HexColor('#687A72')); canvas.drawCentredString(w/2,.32*inch,f'Plan de pruebas · FusaShop · 23 de octubre de 2026  |  {docobj.page}')
    canvas.restoreState()
pdf=SimpleDocTemplate(str(out),pagesize=letter,rightMargin=.78*inch,leftMargin=.78*inch,topMargin=.63*inch,bottomMargin=.55*inch,title='QA render Plan FusaShop')
pdf.build(story,onFirstPage=page,onLaterPages=page)
print(out)
