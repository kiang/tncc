package tw.g0v

import org.apache.pdfbox.exceptions._
import org.apache.pdfbox.pdmodel._
import org.apache.pdfbox.pdmodel.common.PDStream
import org.apache.pdfbox.util._

import scala.collection.JavaConverters._
import scala.io._
import java.io._

case class PDFFile(id: Int, file: String, page: Int)

object Extractor extends App {
  new java.io.File("pdf").listFiles.filter(_.getName.endsWith(".pdf")).foreach((f) => {
    println(s"processing: ${f}")
    val doc = PDDocument.load(f)
    val pages: List[PDPage] = doc.getDocumentCatalog().getAllPages().asScala.toList.asInstanceOf[List[PDPage]]
    var pNum = 0
    pages.foreach((p) => {
      pNum+=1
      val contents = p.getContents
      val printer = new PrintTextLocations(new File(s"${f}_${pNum}.csv"))
      if (contents != null) {
        printer.processStream(p, p.findResources, p.getContents.getStream)
      }
      printer.close
    })
    doc.close
  })
}

class PrintTextLocations(val f: File) extends PDFTextStripper {
  val out = new PrintWriter(f, "UTF-8")

  def quoteIfNeeded(s: String): String = {
    val needToQuote = s.contains("\"") || s.contains(",")
    if (needToQuote) {
      "\"" + s.replace("\"", "\"\"") + "\""
    } else s
  }

  override def processTextPosition(text: TextPosition) =
  {
    out.println(quoteIfNeeded(text.getCharacter) + "," + 
                text.getXDirAdj + "," + 
                text.getYDirAdj + "," + 
                text.getFontSize + "," +
                text.getXScale + "," +
                text.getHeightDir + "," +
                text.getWidthOfSpace + "," +
                text.getWidthDirAdj)
  }

  def close = {
    out.close
  }
}