import tabula
import tempfile
import sys
import font

print (sys.argv[1])

#tabula.environment_info()
 
pdf_path = 'uploads/user/2/20200401_2_1585722851.pdf'
# Times regular 12
pdf_path.font('Times')
# Arial bold 14
pdf_path.font('Arial', 'B', 14)
# Removes bold
pdf_path.font('')
# Times bold, italic and underlined 14
pdf_path.font('Times', 'BIU')

df=tabula.read_pdf(pdf_path,  pages="all")
#temp_dir = tempfile.mkdtemp()
#tabula.convert_into_by_batch(temp_dir, output_format="csv", pages='all')
#tabula.convert_into("open.pdf", "test.csv", output_format="csv", pages='all')
 
print(df)

#import tabula  
#df=tabula.read_pdf("open.pdf", pages='all')
#df
#convert_into('open.pdf', 'test.csv', output_format="csv",pages='all')

# Read pdf into list of DataFrame
#df = tabula.read_pdf("test.pdf", pages='all')

# Read remote pdf into list of DataFrame
#df2 = tabula.read_pdf("https://github.com/tabulapdf/tabula-java/raw/master/src/test/resources/technology/tabula/arabic.pdf")
#print(df2);
# convert PDF into CSV file
#tabula.convert_into("https://github.com/tabulapdf/tabula-java/raw/master/src/test/resources/technology/tabula/arabic.pdf", "output.csv", output_format="csv", pages='all')

# convert all PDFs in a directory
#tabula.convert_into_by_batch("input_directory", output_format='csv', pages='all)